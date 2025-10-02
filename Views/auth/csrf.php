<?php

declare(strict_types=1);

use App\Security\CSRF;

// Set JSON header
header('Content-Type: application/json');

// Enable CORS for development - handle multiple localhost ports
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'http://localhost:3000',
    'http://localhost:3001', 
    'http://localhost:3002',
    'http://localhost:3003'
];

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    http_response_code(200);
    exit;
}

try {
    // Generate or get existing CSRF token
    $csrfToken = CSRF::create();
    
    // Return the token
    echo json_encode([
        'success' => true,
        'csrf_token' => $csrfToken,
        'message' => 'CSRF token generated successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to generate CSRF token',
        'message' => $e->getMessage()
    ]);
}