<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    private static ?DatabaseSessionHandler $handler = null;
    
    public static function start(): void
    {
        // Only start session if the consent cookie is set and the value is accept
        //if (isset($_COOKIE['cookie-consent']) && $_COOKIE['cookie-consent'] === 'accept') {
            $httpsActive = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            $secure = (str_contains($_SERVER['HTTP_HOST'] ?? '', 'localhost') || str_contains($_SERVER['HTTP_HOST'] ?? '', '[::1]')) ? false : $httpsActive;
            // Session name based on secure connection
            $sesstionName = $secure ? '__Secure-SSID' : 'SSID';
            // Define the domain based on localhost or actual host
            $domain = (str_contains($_SERVER['HTTP_HOST'] ?? '', 'localhost') || str_contains($_SERVER['HTTP_HOST'] ?? '', '[::1]')) ? 'localhost' : $_SERVER['HTTP_HOST'] ?? '';
            
            // Use database session handler for distributed environments
            $sessionStorage = defined('SESSION_STORAGE') ? SESSION_STORAGE : 'database';
            if ($sessionStorage === 'database') {
                self::$handler = new DatabaseSessionHandler();
                session_set_save_handler(self::$handler, true);
            }
            // If 'file', use PHP's default file-based session handling
            
            // Set session name
            session_name($sesstionName);
            // Set session cookie parameters
            session_set_cookie_params(
                [
                'lifetime' => \AUTH_EXPIRY,
                'path' => '/',  // Available throughout the site
                'domain' => $domain,  // Ensure correct domain
                'secure' => $secure,  // Only secure on HTTPS
                'httponly' => true,  // Prevent JavaScript access
                'samesite' => ($secure) ? 'None' : 'Lax' // Set to None because of trip to MS Azure AD authentication endpoint and back but None cannot be used with secure false.
                ]
            );
            session_start();
        //}
    }
    // Reset the session
    public static function reset(): void
    {
        session_unset();
        // Only destroy the session if it is active
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
    }
}
