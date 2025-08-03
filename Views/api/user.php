<?php

declare(strict_types=1);

use Controllers\User;
use App\Api\Checks;
use App\Api\Response;
use App\Authentication\JWT;
use App\Authentication\AuthToken;
use App\Logs\SystemLog;

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

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = Checks::jsonBody();
    if (!isset($routeInfo[2]['id'])) {
        Response::output('Missing user id', 400);
        exit();
    }

    $userId = (int) $routeInfo[2]['id'];

    $checks = new Checks($loginInfo, $data);
    $checks->apiChecks();

    $user = new User();
    $dbUserData = $user->get($userId);

    $tokenData = JWT::parseTokenPayLoad(AuthToken::get());
    $dbUserDataFromToken = $tokenData['preferred_username'] ?? $tokenData['username'] ?? $tokenData['email'];

    if ($dbUserData['username'] !== $dbUserDataFromToken) {
        Response::output('You cannot edit another user data', 401);
    }

    if (isset($data['password']) && isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
        Response::output('Passwords do not match', 400);
    }

    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    if (isset($data['role']) && !in_array('administrator', $tokenData['roles'])) {
        Response::output('Only administrators can change roles', 401);
    }

    unset($data['confirm_password'], $data['csrf_token'], $data['username']);

    if (isset($data['picture']) && empty($data['picture'])) {
        $currentPicture = $user->get($userId)['picture'];
        $profilePicturePath = ROOT . '/public' . $currentPicture;
        if (file_exists($profilePicturePath)) {
            unlink($profilePicturePath);
        } else {
            SystemLog::write('Could not delete the picture: ' . $profilePicturePath . '. Full payload was ' . json_encode($data), 'error');
        }
    }
    try {
        $rowCount = $user->update($data, $userId);
        if ($rowCount === 0) {
            Response::output('user not updated', 400);
        }
        Response::output('user updated', 200);
    } catch (\Throwable $e) {
        Response::output('Invalid field: ' . $e->getMessage(), 400);
    }
}

// Handle DELETE requests
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $controller->delete($routeInfo, $loginInfo);
}
