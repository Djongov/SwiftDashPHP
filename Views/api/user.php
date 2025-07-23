<?php

declare(strict_types=1);

use Controllers\User;
use App\Api\Checks;
use App\Api\Response;
use App\Exceptions\UserExceptions;

$controller = new User();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array_map('trim', $_POST);
    $expectedParams = [
        'username',
        'email',
        'password',
        'confirm_password',
        'name',
        'csrf_token'
    ];
    $checks = new Checks($loginInfo, $data);
    $checks->checkParams($expectedParams, $data);
    if ($data['password'] !== $data['confirm_password']) {
        Response::output('Passwords do not match', 400);
    }
    // htmlspecialchars to prevent XSS
    //$data = array_map('htmlspecialchars', $data);
    $data['last_ips'] = currentIP();
    $data['origin_country'] = getUserCountry();
    $data['role'] = 'user'; // Default role
    $data['theme'] = COLOR_SCHEME; // Default theme
    $data['provider'] = 'local'; // Default provider
    $data['enabled'] = true; // Default enabled status
    $controller->create($data, 'local');
}

// Handle DELETE requests
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $controller->delete($routeInfo, $loginInfo);
}
