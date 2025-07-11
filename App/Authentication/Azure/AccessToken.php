<?php

declare(strict_types=1);

namespace App\Authentication\Azure;

use Models\Core\DBCache;
use App\Authentication\JWT;

class AccessToken
{
    public static function dbGet(string $username): array
    {
        $cachedToken = DBCache::get('access_token', $username);
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
            // Let's check if the token is expired
            if (self::isTokenExpired($cachedToken['expiration'])) {
                // If it is expired, let's delete it
                try {
                    DBCache::delete('access_token', $username);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
                // And fetch a new one
                $data = [
                    'state' => $_SERVER['REQUEST_URI'],
                    'username' => $username,
                    'scope' => $scope
                ];
                
                header('Location: /auth/azure/request-access-token?' . http_build_query($data));
                exit();
            } else {
                // Now that we know it's not expired, let's parse it so we can see if it's the right scope
                $parsedToken = JWT::parseTokenPayLoad($cachedToken['value']);
                if (!$parsedToken) {
                    // It must be a mslive token, so we will not decode it, directly return it
                    return $cachedToken['value'];
                }
                // If it is a mslive token it will not be decoded
                if (!self::isScopeMatchingAudience($parsedToken['aud'], $scope)) {
                    // If the audience is not the same, let's delete it
                    try {
                        DBCache::delete('access_token', $username);
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }
                    // And fetch a new one
                    $data = [
                        'state' => $_SERVER['REQUEST_URI'],
                        'username' => $username,
                        'scope' => $scope
                    ];
                    header('Location: /auth/azure/request-access-token?' . http_build_query($data));
                    exit();
                } else {
                    // If not expired AND the right scope, let's return the token
                    return $cachedToken['value'];
                }
            }
            return $cachedToken['value'];
        } else {
            // If no token is present, let's go fetch one
            // This will go to a special endpoint where the user will be asked to consent and get an access token after which it will be saved to the DB
            $data = [
                'state' => $_SERVER['REQUEST_URI'],
                'username' => $username,
                'scope' => $scope
            ];
            
            header('Location: /auth/azure/request-access-token?' . http_build_query($data));
            exit();
        }
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
        $existingToken = self::dbGet($username);
        // If the username doesn't have an access token
        if (!$existingToken) {
            try {
                return DBCache::create($token, $expiration, 'access_token', $username);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        } else {
            // If they have an access token, let's check if the aud is proper.
            $existingTokenParsed = JWT::parseTokenPayLoad($existingToken['value']);
            if ($parsedToken['aud'] === $existingTokenParsed['aud']) {
                try {
                    return DBCache::update($token, $expiration, 'access_token', $username);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
            } else {
                // If the aud is not the same, let's create a new one
                try {
                    return DBCache::create($token, $expiration, 'access_token', $username);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
            }
        }
    }
}
