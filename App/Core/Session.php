<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    public static function start(): void
    {
        // Only start session if the consent cookie is set and the value is accept
        //if (isset($_COOKIE['cookie-consent']) && $_COOKIE['cookie-consent'] === 'accept') {
            $httpsActive = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            $secure = (str_contains($_SERVER['HTTP_HOST'], 'localhost') || str_contains($_SERVER['HTTP_HOST'], '[::1]')) ? false : $httpsActive;
            // Session name based on secure connection
            $sesstionName = $secure ? 'SSID' : 'SSID';
            // Define the domain based on localhost or actual host
            // For cross-origin requests from localhost React app, don't set domain
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            $isLocalDev = str_contains($origin, 'localhost') || 
                         str_contains($_SERVER['HTTP_HOST'], 'localhost') || 
                         str_contains($_SERVER['HTTP_HOST'], '[::1]');
            
            $domain = $isLocalDev ? null : $_SERVER['HTTP_HOST'];
            // Set session name
            session_name($sesstionName);
            // Set session cookie parameters
            $cookieParams = [
                'lifetime' => 86400,  // 1 day
                'path' => '/',  // Available throughout the site
                'secure' => $secure,  // Only secure on HTTPS
                'httponly' => true,  // Prevent JavaScript access
            ];
            
            // Only set domain if not in local development
            if ($domain !== null) {
                $cookieParams['domain'] = $domain;
            }
            
            // Set SameSite policy based on environment
            if ($secure && !$isLocalDev) {
                $cookieParams['samesite'] = 'None'; // For production HTTPS with cross-origin
            } elseif ($isLocalDev) {
                $cookieParams['samesite'] = 'None'; // For cross-origin localhost development
                // Force secure to true for cross-origin development even on HTTP
                $cookieParams['secure'] = true;
            } else {
                $cookieParams['samesite'] = 'Lax'; // For same-origin HTTP
            }
            
            // Force clear any default session domain settings
            if ($isLocalDev) {
                ini_set('session.cookie_domain', '');
            }
            
            session_set_cookie_params($cookieParams
            );
            
            // Debug: Check what domain is actually being set
            error_log('Session cookie params: ' . json_encode($cookieParams));
            error_log('HTTP_ORIGIN: ' . ($_SERVER['HTTP_ORIGIN'] ?? 'not set'));
            error_log('HTTP_HOST: ' . ($_SERVER['HTTP_HOST'] ?? 'not set'));
            error_log('PHP ini session.cookie_domain: ' . ini_get('session.cookie_domain'));
            
            session_start();
            
            // Debug: Check actual cookie params after session start
            $actualParams = session_get_cookie_params();
            error_log('Actual session cookie params after start: ' . json_encode($actualParams));
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
