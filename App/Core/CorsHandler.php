<?php

declare(strict_types=1);

namespace App\Core;

class CorsHandler
{
    public static function handle(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Always allow localhost origins for development
        $allowedOrigins = defined('CORS_ALLOWED_ORIGINS') ? CORS_ALLOWED_ORIGINS : [];
        $allowedOrigins = array_merge($allowedOrigins, [
            'http://localhost:3000',
            'http://localhost:3001',
            'http://localhost:3002',
            'http://localhost:3003'
        ]);
        
        // Check if origin is allowed
        if (in_array($origin, $allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
        header('Access-Control-Max-Age: 86400'); // Cache preflight for 24 hours
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
    
    public static function isPreflightRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'OPTIONS' &&
               !empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']);
    }
}
