<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Core\Cookies;

class AuthToken
{
    public static function get(): ?string
    {
        if (AUTH_HANDLER === 'cookie') {
            return $_COOKIE[AUTH_COOKIE_NAME] ?? null;
        } elseif (AUTH_HANDLER === 'session') {
            return $_SESSION[AUTH_SESSION_NAME] ?? null;
        } else {
            return null;
        }
    }
    public static function set($value, $cookieDuration = AUTH_COOKIE_EXPIRY): void
    {
        if (AUTH_HANDLER === 'cookie') {
            Cookies::setAuthCookie($value, $cookieDuration);
        } elseif (AUTH_HANDLER === 'session') {
            $_SESSION[AUTH_SESSION_NAME] = $value;
        }
    }
    public static function unset(): void
    {
        if (AUTH_HANDLER === 'cookie') {
            unset($_COOKIE[AUTH_COOKIE_NAME]);
            $host = $_SERVER['HTTP_HOST'];
            $colonPos = strstr($host, ':');
            $cleanedHost = $colonPos !== false ? str_replace($colonPos, '', $host) : $host;

            setcookie(AUTH_COOKIE_NAME, '', -1, '/', $cleanedHost);
        } elseif (AUTH_HANDLER === 'session') {
            unset($_SESSION[AUTH_SESSION_NAME]);
        }
    }
}
