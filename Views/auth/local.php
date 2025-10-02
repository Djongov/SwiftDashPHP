<?php

declare(strict_types=1);

use Controllers\User;
use App\Api\Response;
use App\Exceptions\UserExceptions;
use App\Api\Checks;
use App\Logs\SystemLog;
use App\Authentication\JWT;
use App\Authentication\AuthToken;

$data = App\Api\Checks::jsonBody();

// If the request is coming from local login, we should have a $_POST['username'] and a $_POST['password'] parameter
if (isset($data['username'], $data['password'], $data['csrf_token'])) {
    // First check the CSRF token
    $checks = new Checks($loginInfo, $data);
    //$checks->checkCSRF($data['csrf_token']);

    // You can implement a sleep here, to slow down the response to and therefore slow down potential spam on the login form
    sleep(0);

    $user = new User();

    try {
        $userArray = $user->get($data['username']);
    } catch (UserExceptions $e) {
        Response::output($e->getMessage(), 400);
    }

    if (empty($userArray)) {
        Response::output('Invalid username or password', 404); // Do not say if the user exists or not to reduce the risk of enumeration attacks
    }

    if ($userArray['enabled'] === '0') {
        Response::output('User is disabled', 401);
    }

    if (!password_verify($data['password'], $userArray['password'])) {
        Response::output('Invalid username or password', 404);
    }

    // By now we assume the user is valid, so let's generate a JWT token
    $idToken = JWT::generateToken(
        [
        'provider' => 'local',
        'username' => $userArray['username'],
        'name' => $userArray['name'],
        'roles' => [
            $userArray['role'],
        ],
        'last_ip' => currentIP()
        ], JWT_TOKEN_EXPIRY
    );

    AuthToken::set($idToken);
    // Record last login
    $user->updateLastLogin($userArray['username']);

    // Return success response with token and user data for React
    Response::output([
        'success' => true,
        'message' => 'Login successful',
        'token' => $idToken,
        'session_id' => session_id(),
        'data' => [
            'username' => $userArray['username'],
            'name' => $userArray['name'],
            'picture' => $userArray['picture'] ?? null,
            'isAdmin' => $userArray['role'] === 'admin',
            'provider' => 'local'
        ]
    ]);
} else {
    Response::output('Invalid request', 400);
}
