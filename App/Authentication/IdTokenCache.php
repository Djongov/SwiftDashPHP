<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Authentication\JWT;
use Models\Core\DBCache;

class IdTokenCache
{
    // Check if there is a token in the cache for that username
    public static function exist(string $username): bool
    {
        $cachedToken = DBCache::get('id_token', $username);
        return ($cachedToken) ? true : false;
    }
    public static function get(string $username): array
    {
        $cachedToken = DBCache::get('id_token', $username);
        return ($cachedToken) ? $cachedToken : [];
    }
    public static function save(string $token): void
    {
        // The cache is keyed by username, so resolve it first (the old code looked the token string up)
        $parsedToken = JWT::parseTokenPayLoad($token);
        $username = $parsedToken['upn'] ?? $parsedToken['username'] ?? $parsedToken['email'] ?? null;
        if ($username === null || !isset($parsedToken['exp'])) {
            return;
        }
        if (!self::exist($username)) {
            // Set the expiration to the token's expiration but convert to mysql datetime
            $expiration = date('Y-m-d H:i:s', (int) $parsedToken['exp']);
            DBCache::create($token, $expiration, 'id_token', $username);
        }
    }
    public static function update(string $token, string $username): void
    {
        $parsedToken = JWT::parseTokenPayLoad($token);
        // Set the expiration to the token's expiration but convert to mysql datetime
        $expiration = date('Y-m-d H:i:s', $parsedToken['exp']);
        DBCache::update($token, $expiration, 'id_token', $username);
    }
}
