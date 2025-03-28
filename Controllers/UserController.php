<?php

declare(strict_types=1);

namespace Controllers;

use App\Api\Response;
use App\Api\Checks;
use App\Authentication\JWT;
use App\Authentication\AuthToken;
use App\Logs\SystemLog;
use Models\User;

class UserController
{
    public function getUser(array $routeInfo, array $loginInfo): void
    {
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

        if (!isset($routeInfo[2]['id'])) {
            Response::output('Missing user id', 400);
            exit();
        }

        $userId = $routeInfo[2]['id'];
        $userId = !is_numeric($userId) ? (string) $userId : (int) $userId;

        $userInfoArray = $user->get($userId);

        if ($userInfoArray) {
            Response::output($userInfoArray, 200);
        } else {
            Response::output('User not found', 404);
        }
    }

    public function createUser(array $loginInfo): void
    {
        if (!MANUAL_REGISTRATION) {
            Response::output('Manual registration is disabled', 400);
            exit();
        }

        $checks = new Checks($loginInfo, $_POST);
        $checks->apiChecksNoUser();

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

        echo $user->create($data, 'local');
    }

    public function updateUser(array $routeInfo, array $loginInfo): void
    {
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
            $profilePicturePath = dirname($_SERVER['DOCUMENT_ROOT']) . '/public' . $currentPicture;
            if (file_exists($profilePicturePath)) {
                unlink($profilePicturePath);
            } else {
                SystemLog::write('Could not delete the picture: ' . $profilePicturePath . '. Full payload was ' . json_encode($data), 'error');
            }
        }

        $user->update($data, (int) $userId);
    }

    public function deleteUser(array $routeInfo): void
    {
        if (!isset($_GET['csrf_token'])) {
            Response::output('Missing CSRF Token', 401);
            exit();
        }

        if (!isset($routeInfo[2]['id'])) {
            Response::output('Missing user id', 400);
            exit();
        }

        $userId = (int) $routeInfo[2]['id'];

        $checks = new Checks([], []);
        $checks->apiChecksDelete($_GET['csrf_token']);

        $tokenData = JWT::parseTokenPayLoad(AuthToken::get());

        $user = new User();
        $dbUserData = $user->get($userId);

        $dbUserDataFromToken = $tokenData['preferred_username'] ?? $tokenData['username'] ?? $tokenData['email'];

        if ($dbUserData['username'] !== $dbUserDataFromToken) {
            Response::output('You cannot delete another user', 401);
        }

        $user->delete($userId);
    }
}
