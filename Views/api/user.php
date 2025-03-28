<?php

declare(strict_types=1);

use App\Api\Response;
use App\Api\Checks;
use Controllers\User;
use App\Authentication\JWT;
use App\Authentication\AuthToken;
use App\Logs\SystemLog;

// GET /api/user
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $checks = new Checks($loginInfo, []);
    $checks->apiChecksNoCSRF();

    $user = new User();

    if (!$routeInfo[2]) {
        $allUsers = $user->get(null);
        if ($allUsers) {
            Response::output($allUsers, 200);
        } else {
            Response::output('No users found', 404);
        }
        return;
    }

    // This endpoint is for fetching a user's data
    if (!isset($routeInfo[2]['id'])) {
        Response::output('Missing user id', 400);
        exit();
    }

    // If the user is integer, then we will assume it's an id, otherwise we'll assume it's a username
    $userId = $routeInfo[2]['id'];
    if (!is_numeric($userId)) {
        $userId = (string) $userId;
    } else {
        $userId = (int) $userId;
    }

    $userInfoArray = $user->get($userId);

    if ($userInfoArray) {
        Response::output($userInfoArray, 200);
    } else {
        Response::output('User not found', 404);
    }
}

// POST /api/user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!MANUAL_REGISTRATION) {
        Response::output('Manual registration is disabled', 400);
        exit();
    }
    // This endpoint is for creating a new local user.
    $checks = new Checks($loginInfo, $_POST);
    $checks->apiChecksNoUser();

    // Create the user
    $user = new User();

    $data = $_POST;

    unset($data['csrf_token']);

    $requiredFields = ['username', 'password', 'confirm_password', 'email'];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            Response::output('Missing ' . $field, 400);
            exit();
        }
        if (empty($data[$field])) {
            Response::output('Empty ' . $field, 400);
            exit();
        }
    }

    $data['last_ips'] = currentIP();

    $data['origin_country'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : 'EN';

    $data['role'] = 'user';

    $data['theme'] = COLOR_SCHEME;

    $data['provider'] = 'local';

    $data['enabled'] = 1;

    // Decide whether you want to sleep here or not, to slow down the brute force attacks
    // sleep(2);

    echo $user->create($data, 'local');
}


if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Let's catch php input stream
    $data = Checks::jsonBody();

    // Also the router info should bring us the id
    if (!isset($routeInfo[2]['id'])) {
        Response::output('Missing user id', 400);
        exit();
    }

    $userId = (int) $routeInfo[2]['id'];

    $checks = new Checks($loginInfo, $data);
    $checks->apiChecks();

    // Get the user data based on the ID
    $user = new User();

    $dbUserData = $user->get($userId);

    // Now let's parse the token
    $tokenData = JWT::parseTokenPayLoad(AuthToken::get());

    if (isset($tokenData['preferred_username'])) {
        $dbUserDataFromToken = $tokenData['preferred_username'];
    } elseif (isset($tokenData['username'])) {
        $dbUserDataFromToken = $tokenData['username'];
    } else {
        // fallback to email
        $dbUserDataFromToken = $tokenData['email'];
    }

    // Check if user id in path matches the one in the token, unless the user is an admin
    if ($dbUserData['username'] !== $dbUserDataFromToken) {
        Response::output('You cannot edit another user data', 401);
    }

    if (isset($data['password']) && isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
        Response::output('Passwords do not match', 400);
    }

    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    // There is a token claim for roles which is an array of roles, let's look for an administrator role
    if (isset($data['role'])) {
        if (!in_array('administrator', $tokenData['roles'])) {
            Response::output('only administrators can change roles', 401);
        }
    }


    unset($data['confirm_password']);
    unset($data['csrf_token']);
    unset($data['username']);

    // If we are deleting the picutre, we need to remove it from the filesystem
    if (isset($data['picture'])) {
        if ($data['picture'] === '' || $data['picture'] === 'null' || $data['picture'] === null) {
            // Now let's find the current picture name
            $currentPicture = $user->get($userId)['picture'];
            $profilePicturePath = dirname($_SERVER['DOCUMENT_ROOT']) . '/public' . $currentPicture;
            // Now delete the file
            if (file_exists($profilePicturePath)) {
                unlink($profilePicturePath);
            } else {
                SystemLog::write('Could not delete the picture: ' . $profilePicturePath . '. Full payload was ' . json_encode($data), 'error');
            }
        }
    }

    $user->update($data, (int) $userId);
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Let's check if the csrf token is passed as a query string in the DELETE request
    if (!isset($_GET['csrf_token'])) {
        Response::output('Missing CSRF Token', 401);
        exit();
    }

    // Also the router info should bring us the id
    if (!isset($routeInfo[2]['id'])) {
        Response::output('Missing user id', 400);
        exit();
    }

    $userId = (int) $routeInfo[2]['id'];

    $checks = new Checks($loginInfo, []);
    $checks->apiChecksDelete($_GET['csrf_token']);

    $tokenData = JWT::parseTokenPayLoad(AuthToken::get());

    $dbUserData = $user->get($userId);

    // Let's comapre
    if (isset($tokenData['preferred_username'])) {
        $dbUserDataFromToken = $tokenData['preferred_username'];
    } elseif (isset($tokenData['username'])) {
        $dbUserDataFromToken = $tokenData['username'];
    } else {
        // fallback to email
        $dbUserDataFromToken = $tokenData['email'];
    }

    if ($dbUserData['username'] !== $dbUserDataFromToken) {
        Response::output('You cannot delete another user', 401);
    }

    $user = new User();

    $user->delete($userId);
}
