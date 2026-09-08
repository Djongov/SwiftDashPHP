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
    // Self-registration is only possible when local login and manual registration are enabled
    if (!LOCAL_USER_LOGIN || !defined('MANUAL_REGISTRATION') || !MANUAL_REGISTRATION) {
        Response::output('Registration is disabled', 403);
    }
    $data = array_map(fn($value) => is_string($value) ? trim($value) : $value, $_POST);
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
    // The token must match the session, not merely be present
    $checks->checkCSRF();
    if ($data['password'] !== $data['confirm_password']) {
        Response::output('Passwords do not match', 400);
    }
    // htmlspecialchars to prevent XSS
    //$data = array_map('htmlspecialchars', $data);
    $data['last_ips'] = currentIP();
    $data['origin_country'] = getUserCountry() ?? 'EN';
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

    // Only administrators may touch privilege-related columns
    $protectedColumns = ['id', 'role', 'provider', 'enabled', 'date_created', 'last_updated'];
    foreach ($protectedColumns as $column) {
        if (array_key_exists($column, $data) && !$loginInfo['isAdmin']) {
            Response::output('Only administrators can change ' . $column, 401);
        }
    }

    unset($data['confirm_password'], $data['csrf_token'], $data['username']);

    if (isset($data['picture']) && empty($data['picture'])) {
        $currentPicture = (string) ($user->get($userId)['picture'] ?? '');
        $profilePicturePath = User::resolveProfilePicturePath($currentPicture);
        // Only files that really live in the profile pictures folder may be deleted
        if ($profilePicturePath !== null) {
            unlink($profilePicturePath);
        } else {
            SystemLog::write('Could not delete the picture: ' . $currentPicture . '. Full payload was ' . json_encode($data), 'error');
        }
    }
    try {
        // The controller sends the API response itself
        $user->update($data, $userId);
    } catch (\Throwable $e) {
        Response::output('Invalid field: ' . $e->getMessage(), 400);
    }
}

// Handle DELETE requests
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Check if body is empty (CONTENT_LENGTH is not set at all on most empty requests)
    if (!empty($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        Response::output('body must be empty in DELETE requests', 400);
        exit();
    }
    // Let's check if the csrf token is passed as a query string in the DELETE request
    if (!isset($_GET['csrf_token'])) {
        Response::output('missing csrf token', 401);
        exit();
    }

    // Also the router info should bring us the id
    if (!isset($routeInfo[2]['id'])) {
        Response::output('missing id parameter', 400);
        exit();
    }

    $checks = new Checks($loginInfo, $_GET);

    // Login, token integrity and CSRF (from the query string, DELETE has no body)
    $checks->apiChecksDelete($_GET['csrf_token'], false);

    $id = (int) $routeInfo[2]['id'];

    // Users may only delete themselves, administrators may delete anyone
    $targetUser = $controller->get($id);
    if (!$loginInfo['isAdmin'] && $targetUser['username'] !== JWT::extractUserName(AuthToken::get())) {
        Response::output('You cannot delete another user', 401);
    }

    try {
        $controller->delete($id);
    } catch (\Throwable $e) {
        Response::output('Error deleting user: ' . $e->getMessage(), 400);
    }
}

Response::output('Invalid api action', 400);
