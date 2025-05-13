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
        $checks = new Checks($loginInfo, $_GET);
        $checks->apiChecksNoCSRF(false);

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

        try {
            $dbUserData = $user->get($userId);

            $tokenData = JWT::parseTokenPayLoad(AuthToken::get());
            $dbUserDataFromToken = $tokenData['preferred_username'] ?? $tokenData['username'] ?? $tokenData['email'];
            // Do not allow users to view other users data unless they are an administrator
            if ($dbUserData['username'] !== $dbUserDataFromToken && !$loginInfo['isAdmin']) {
                if (ERROR_VERBOSE) {
                    Response::output('You cannot view another user data', 401);
                } else {
                    Response::output('User not found', 404);
                }
            }
        } catch (\Throwable $e) {
            SystemLog::write('User GET error: ' . $e->getMessage(), 'User GET error');
            if (ERROR_VERBOSE) {
                Response::output($e->getMessage(), $e->getCode());
            } else {
                Response::output('User not found', 404);
            }
        }

        if ($dbUserData) {
            Response::output($dbUserData, 200);
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

        $requiredFields = ['username', 'password', 'confirm_password', 'email', 'name'];

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

        try {
            $newUserId = $user->create($data); // Create the user in the database and return the new user ID
        } catch (\Throwable $e) {
            Response::output('User not created: ' . $e->getMessage(), $e->getCode());
        }

        if ($newUserId) {
            $userInfoArray = $user->get($newUserId);
            Response::output($userInfoArray, 201);
        } else {
            Response::output('User not created', 400);
        }
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
            Response::output(translate('user_api_response_cannot_edit_other_user_data'), 401);
        }

        if (isset($data['password']) && isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
            Response::output(translate('user_api_response_passwords_do_not_match'), 400);
        }

        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (isset($data['role']) && !in_array('administrator', $tokenData['roles'])) {
            Response::output(translate('user_api_response_only_admins_change_roles'), 401);
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
                Response::output(translate('user_api_response_user_not_updated'), 400);
            }
            Response::output(translate('user_api_response_user_updated'), 200);
        } catch (\Throwable $e) {
            Response::output('Invalid field: ' . $e->getMessage(), 400);
        }
    }

    public function deleteUser(array $routeInfo, array $loginInfo): void
    {
        if (!isset($_GET['csrf_token'])) {
            Response::output(translate('api_response_missing_csrf_token'), 401);
            exit();
        }

        if (!isset($routeInfo[2]['id'])) {
            Response::output(translate('api_response_missing_user_id'), 400);
            exit();
        }

        $userId = (int) $routeInfo[2]['id'];

        $checks = new Checks($loginInfo, []);
        $checks->apiChecksDelete($_GET['csrf_token']);

        $tokenData = JWT::parseTokenPayLoad(AuthToken::get());

        $user = new User();
        $dbUserData = $user->get($userId);

        $dbUserDataFromToken = $tokenData['preferred_username'] ?? $tokenData['username'] ?? $tokenData['email'];

        if ($dbUserData['username'] !== $dbUserDataFromToken) {
            Response::output(translate('user_api_response_user_cannot_delete_another_user'), 401);
        }

        $user->delete($userId);
    }
}
