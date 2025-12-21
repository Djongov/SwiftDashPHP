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
            
            // Only extend cookie if duration is significantly different (to avoid extending on every request)
            // Check if cookie needs extending (more than 1 minute difference)
            if (abs($cookieDuration - AUTH_COOKIE_EXPIRY) > 60) {
                self::extendSessionCookie($cookieDuration);
            }
        }
    }

    private static function extendSessionCookie(int $cookieDuration): void
    {
        // Only regenerate session ID if we're using database sessions
        // With file-based sessions, regenerate_id is safe, but with database
        // sessions it can create duplicate entries if called too frequently
        $sessionStorage = defined('SESSION_STORAGE') ? SESSION_STORAGE : 'file';
        
        // Only regenerate if enough time has passed since last regeneration
        // to avoid creating too many session entries
        if ($sessionStorage === 'file') {
            // For file-based sessions, always regenerate for security
            session_regenerate_id(true);
        } else {
            // For database sessions, only regenerate if it hasn't been done recently
            // Check if we've regenerated in the last 5 minutes
            $lastRegeneration = $_SESSION['_last_regeneration'] ?? 0;
            if (time() - $lastRegeneration > 300) { // 5 minutes
                session_regenerate_id(true);
                $_SESSION['_last_regeneration'] = time();
            }
        }
        
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
