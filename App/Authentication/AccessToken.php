<?php

declare(strict_types=1);

namespace App\Authentication;

use Models\Core\DBCache;

class AccessToken
{
    public static function dbGet(string $username): array
    {
        $cachedToken = DBCache::get('access_token', $username);
        return ($cachedToken) ? $cachedToken : [];
    }
    public static function get(string $username, $scope = 'https://graph.microsoft.com/user.read'): string
    {
        $cachedToken = self::dbGet($username);
        if ($cachedToken) {
            // Let's check if the token is expired
            if ($cachedToken['expiration'] < date('Y-m-d H:i:s')) {
                // If it is expired, let's delete it
                try {
                    DBCache::delete('access_token', $username);
                } catch (\Exception $e) {
                    throw new \Exception('Error deleting token from cache');
                }
                // And fetch a new one
                $data = [
                    'state' => $_SERVER['REQUEST_URI'],
                    'username' => $username,
                ];
                if ($scope !== 'https://graph.microsoft.com/user.read') {
                    $data['scope'] = $scope;
                }
                header('Location: /auth/azure/request-access-token?' . http_build_query($data));
                exit();
            } else {
                // Now that we know it's not expired, let's parse it so we can see if it's the right scope
                $parsedToken = JWT::parseTokenPayLoad($cachedToken['value']);
                // If it is a mslive token it will not be decoded
                if ($parsedToken['aud'] !== $scope) {
                    // If the audience is not the same, let's delete it
                    try {
                        DBCache::delete('access_token', $username);
                    } catch (\Exception $e) {
                        throw new \Exception('Error deleting token from cache');
                    }
                    // And fetch a new one
                    $data = [
                        'state' => $_SERVER['REQUEST_URI'],
                        'username' => $username,
                    ];
                    if ($scope !== 'https://graph.microsoft.com/user.read') {
                        $data['scope'] = $scope;
                    }
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
            ];
            if ($scope !== 'https://graph.microsoft.com/user.read') {
                $data['scope'] = $scope;
            }
            header('Location: /auth/azure/request-access-token?' . http_build_query($data));
            exit();
        }
    }
    public static function save(string $token, string $username): string
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

        // If the username doesn't have an access token
        if (!self::dbGet($username)) {
            try {
                return DBCache::create($token, $expiration, 'access_token', $username);
            } catch (\Exception $e) {
                throw new \Exception('Error saving token to cache');
            }
        } else {
            // Let's check if the audience is the same
            $tokenInCache = self::dbGet($username);
            $parsedTokenInCache = JWT::parseTokenPayLoad($tokenInCache['value']);
            if (!$parsedToken['aud']) {
                $parsedToken['aud'] = 'https://graph.microsoft.com';
            }
            if ($parsedToken['aud'] === $parsedTokenInCache['aud']) {
                try {
                    return DBCache::update($token, $expiration, 'access_token', $username);
                } catch (\Exception $e) {
                    throw new \Exception('Error updating token in cache');
                }
            } else {
                try {
                    return DBCache::create($token, $expiration, 'access_token', $username);
                } catch (\Exception $e) {
                    throw new \Exception('Error saving token to cache');
                }
            }
        }
    }
}
