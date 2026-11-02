<?php

declare(strict_types=1);

namespace App\Authentication\Azure;

use Models\Core\DBCache;
use App\Authentication\JWT;
use App\Api\Response;
use App\Logs\SystemLog;

class AccessToken
{
    private const ACCESS_TOKEN_TYPE = 'access_token';
    private const REFRESH_TOKEN_TYPE = 'refresh_token';

    /** Renew this many seconds before the access token expires, so it does not run out in the middle of a long API call */
    private const RENEW_BEFORE_SECONDS = 300;

    /** Entra ID refresh tokens for web apps last 90 days (sliding, each use returns a new one) */
    private const REFRESH_TOKEN_LIFETIME = '+90 days';

    public static function dbGet(string $username): array
    {
        $cachedToken = DBCache::get(self::ACCESS_TOKEN_TYPE, $username);
        return ($cachedToken) ? $cachedToken : [];
    }
    public static function isTokenExpired(string $expiration): bool
    {
        $exp = new \DateTime($expiration);
        $now = new \DateTime();
        return $exp < $now;
    }
    public static function isScopeMatchingAudience(string $aud, string $scope): bool
    {
        $scopes = explode(' ', $scope);
        $aud = rtrim($aud, '/');

        foreach ($scopes as $s) {
            $s = trim($s);

            if ($s === $aud) {
                return true;
            }

            if ($s === $aud . '/.default') {
                return true;
            }

            if (str_starts_with($s, $aud . '/')) {
                return true;
            }
        }

        return false;
    }
    public static function get(string $username, string $scope): string
    {
        $cachedToken = self::dbGet($username);
        if ($cachedToken) {
            if (self::isTokenExpired($cachedToken['expiration']) || self::isExpiringSoon($cachedToken['expiration'])) {
                $reason = 'expired on ' . $cachedToken['expiration'];
            } else {
                // Now that we know it's not expired, let's parse it so we can see if it's the right scope
                $parsedToken = JWT::parseTokenPayLoad($cachedToken['value']);
                if (!$parsedToken) {
                    // It must be a mslive token, so we will not decode it, directly return it
                    return $cachedToken['value'];
                }
                if (self::isScopeMatchingAudience($parsedToken['aud'], $scope)) {
                    // If not expired AND the right scope, let's return the token
                    return $cachedToken['value'];
                }
                $reason = 'was issued for ' . $parsedToken['aud'] . ' instead of ' . $scope;
            }
        } else {
            $reason = 'is not in the cache';
        }

        // Try to renew silently with the refresh token first, this also works for a different scope
        $renewedToken = self::renew($username, $scope);
        if ($renewedToken !== null) {
            return $renewedToken;
        }

        if ($cachedToken) {
            try {
                DBCache::delete(self::ACCESS_TOKEN_TYPE, $username);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
        // No usable refresh token, the user has to go through the authorization code flow again
        self::requestNewToken($username, $scope, $reason);
    }
    public static function save(string $token, string $username): int|string
    {
        $parsedToken = JWT::parseTokenPayLoad($token);

        // If it is a mslive token it will not be decoded
        if (!$parsedToken) {
            $parsedToken = [
                'exp' => time() + 3600, // 1 hour
                'aud' => 'https://graph.microsoft.com'
            ];
        }

        $expiration = date('Y-m-d H:i:s', $parsedToken['exp']);
        // One access token per user, a token for another scope replaces the old one. get() only ever reads one row,
        // so keeping both would leave a stale row that hides the new token
        try {
            if (!self::dbGet($username)) {
                return DBCache::create($token, $expiration, self::ACCESS_TOKEN_TYPE, $username);
            }
            return DBCache::update($token, $expiration, self::ACCESS_TOKEN_TYPE, $username);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    public static function saveRefreshToken(string $refreshToken, string $username): void
    {
        $expiration = date('Y-m-d H:i:s', strtotime(self::REFRESH_TOKEN_LIFETIME));
        if (DBCache::get(self::REFRESH_TOKEN_TYPE, $username)) {
            DBCache::update($refreshToken, $expiration, self::REFRESH_TOKEN_TYPE, $username);
        } else {
            DBCache::create($refreshToken, $expiration, self::REFRESH_TOKEN_TYPE, $username);
        }
    }
    // Remove the user's cached tokens, e.g. on logout
    public static function forget(string $username): void
    {
        DBCache::delete(self::ACCESS_TOKEN_TYPE, $username);
        DBCache::delete(self::REFRESH_TOKEN_TYPE, $username);
    }
    private static function isExpiringSoon(string $expiration): bool
    {
        return strtotime($expiration) - self::RENEW_BEFORE_SECONDS < time();
    }
    // Redeem the stored refresh token for a new access token. Returns null when there is none or Entra ID refuses it
    private static function renew(string $username, string $scope): ?string
    {
        $cachedRefreshToken = DBCache::get(self::REFRESH_TOKEN_TYPE, $username);
        if (!$cachedRefreshToken) {
            return null;
        }
        if (self::isTokenExpired($cachedRefreshToken['expiration'])) {
            DBCache::delete(self::REFRESH_TOKEN_TYPE, $username);
            return null;
        }

        $token = CodeExchange::refresh((string) $cachedRefreshToken['value'], $scope);

        if (isset($token['error']) || !isset($token['access_token'], $token['token_type']) || $token['token_type'] !== 'Bearer') {
            // Revoked, expired or the user lost access, do not try this refresh token again
            SystemLog::write('Refreshing the access token for ' . $username . ' failed: ' . ($token['error'] ?? 'unexpected token response'), 'Access Token');
            DBCache::delete(self::REFRESH_TOKEN_TYPE, $username);
            return null;
        }

        self::save($token['access_token'], $username);
        // Entra ID rotates refresh tokens, keep the newest one
        if (!empty($token['refresh_token'])) {
            self::saveRefreshToken($token['refresh_token'], $username);
        }

        return $token['access_token'];
    }
    // Sends the user through /auth/azure/request-access-token to get a new token. API calls are usually made with
    // fetch(), which cannot follow the interactive Microsoft login, so they get a 401 with the reason instead
    private static function requestNewToken(string $username, string $scope, string $reason): never
    {
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        if (str_starts_with($requestPath, '/api/')) {
            SystemLog::write('Access token for ' . $username . ' ' . $reason . ', refused API call to ' . $requestPath, 'Access Token');
            Response::output('Your Azure access token ' . $reason . '. Reload the page to get a new one, then try again', 401);
        }

        $data = [
            'state' => $_SERVER['REQUEST_URI'],
            'username' => $username,
            'scope' => $scope
        ];

        header('Location: /auth/azure/request-access-token?' . http_build_query($data));
        exit();
    }
}
