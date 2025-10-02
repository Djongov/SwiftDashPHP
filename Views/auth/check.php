<?php

declare(strict_types=1);

// Include required files
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/functions.php';

use App\Core\Session;
use App\Authentication\JWT;
use App\Authentication\AuthToken;

// Start session
Session::start();

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3002');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $authenticated = false;
    $userData = null;
    $authMethod = 'none';

    // Check for Authorization header (Bearer token)
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $token = $matches[1];
        try {
            // Validate token and check expiration
            if (JWT::validateToken($token) && !JWT::checkExpiration($token)) {
                $decodedToken = JWT::parseTokenPayLoad($token);
                if ($decodedToken && !empty($decodedToken)) {
                    $authenticated = true;
                    $authMethod = 'bearer_token';
                    $userData = [
                        'username' => $decodedToken['username'] ?? 'unknown',
                        'name' => $decodedToken['name'] ?? 'Unknown User',
                        'picture' => null,
                        'isAdmin' => in_array('admin', $decodedToken['roles'] ?? []) || in_array('administrator', $decodedToken['roles'] ?? []),
                        'provider' => $decodedToken['provider'] ?? 'unknown'
                    ];
                }
            }
        } catch (Exception $e) {
            // Token validation failed - continue to session check
        }
    }

    // Fallback: Check session-based authentication
    if (!$authenticated && isset($_SESSION['auth_session']) && !empty($_SESSION['auth_session'])) {
        try {
            $token = AuthToken::get();
            if ($token && JWT::validateToken($token) && !JWT::checkExpiration($token)) {
                $decodedToken = JWT::parseTokenPayLoad($token);
                if ($decodedToken && !empty($decodedToken)) {
                    $authenticated = true;
                    $authMethod = 'session';
                    $userData = [
                        'username' => $decodedToken['username'] ?? 'unknown',
                        'name' => $decodedToken['name'] ?? 'Unknown User',
                        'picture' => null,
                        'isAdmin' => in_array('admin', $decodedToken['roles'] ?? []) || in_array('administrator', $decodedToken['roles'] ?? []),
                        'provider' => $decodedToken['provider'] ?? 'unknown'
                    ];
                }
            }
        } catch (Exception $e) {
            // Session auth failed
        }
    }

    // Return authentication status
    echo json_encode([
        'success' => true,
        'authenticated' => $authenticated,
        'auth_method' => $authMethod,
        'data' => $userData,
        'message' => $authenticated ? 'User is authenticated' : 'User is not authenticated'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'authenticated' => false,
        'error' => 'Authentication check failed',
        'message' => $e->getMessage()
    ]);
}