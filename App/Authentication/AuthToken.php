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
            
            // If cookieDuration is different from default, extend the session cookie lifetime
            if ($cookieDuration !== AUTH_COOKIE_EXPIRY) {
                self::extendSessionCookie($cookieDuration);
            }
        }
    }

    private static function extendSessionCookie(int $cookieDuration): void
    {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Determine security settings
        $httpsActive = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $secure = (str_contains($_SERVER['HTTP_HOST'], 'localhost') || str_contains($_SERVER['HTTP_HOST'], '[::1]')) ? false : $httpsActive;
        $domain = (str_contains($_SERVER['HTTP_HOST'], 'localhost') || str_contains($_SERVER['HTTP_HOST'], '[::1]')) ? 'localhost' : $_SERVER['HTTP_HOST'];
        
        // Update the existing session cookie with new expiry
        // We can't use session_set_cookie_params() because the session is already active
        setcookie(
            session_name(),
            session_id(),
            time() + $cookieDuration,
            '/',
            $domain,
            $secure,
            true
        );
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
