<?php

declare(strict_types=1);

// This is the model of the User Api
namespace Controllers;

use App\Api\Response;
use App\Api\Checks;
use Models\User as UserModel;
use App\Exceptions\UserExceptions;

class User
{
    public function get(string|int|null $username = null): array
    {
        $user = new UserModel();
        try {
            return $user->get($username);
        } catch (UserExceptions $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: UserExceptions getting user', 'Invalid username or password');
        } catch (\Exception $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: Exception getting user', 'Invalid username or password');
        }
    }
    public function create(array $data, string $provider): void
    {
        if (!in_array($provider, SUPPORTED_AUTH_PROVIDERS)) {
            Response::output('Provider not supported. Must be on of ' . implode(', ', SUPPORTED_AUTH_PROVIDERS), 400);
        }
        if ($provider === 'azure') {
            $this->createAzureUser($data);
        } elseif ($provider === 'mslive') {
            $this->createMsLiveUser($data);
        } elseif ($provider === 'google') {
            $this->createGoogleUser($data);
        } elseif ($provider === 'local') {
            $this->createLocalUser($data);
        }
    }
    public function createAzureUser(array $data, bool $returnResult = true): void
    {
        // $data is the contents of the JWT token, so we need to do some transformations before we can use it
        $insertData = [];
        // usernames comes as preferred_username in the JWT token
        $insertData['username'] = $data['preferred_username'] ?? Response::output('Missing preferred_username in token', 400);
        // Prepare the email, if it's not present in the JWT token, use the username
        $insertData['email'] = $data['email'] ?? $insertData['username'];
        // Name comes as name in the JWT token
        $insertData['name'] = $data['name'] ?? $insertData['username'];
        // Last IPs comes as ipaddr in the JWT token, if it's not present, use the current IP
        $insertData['last_ips'] = $data['ipaddr'] ?? currentIP();
        // If JWT has a claim called 'ctry' take it, otherwise take the browser language
        $insertData['origin_country'] = (isset($data['ctry'])) ? $data['ctry'] : substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        // Role comes as an array of roles in the JWT token, we only need the first one
        $insertData['role'] = $data['roles'][0] ?? 'user';
        // Theme is set to the default color scheme
        $insertData['theme'] = COLOR_SCHEME;
        $insertData['provider'] = 'azure';
        $insertData['enabled'] = 1;

        $allowedData = ['username', 'email', 'name', 'last_ips', 'origin_country', 'role', 'theme', 'provider', 'enabled'];

        $checks = new Checks($allowedData, $insertData);

        $checks->checkParams($allowedData, $insertData);

        try {
            $createUser = new UserModel();
            $createUser->create($insertData);
            if ($returnResult) {
                return;
            }
        } catch (UserExceptions $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: UserExceptions creating azure provider user', 'unable to create user');
        } catch (\Exception $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: Exception creating azure provider user', 'unable to create user');
        }
    }
    public function createMsLiveUser(array $data, bool $returnResult = true): void
    {
        // $data is the contents of the JWT token, so we need to do some transformations before we can use it
        $insertData = [];
        // usernames comes as preferred_username in the JWT token
        $insertData['username'] = $data['preferred_username'] ?? Response::output('Missing preferred_username in token', 400);
        // Prepare the email, if it's not present in the JWT token, use the username
        $insertData['email'] = $data['email'] ?? $insertData['username'];
        // Name comes as name in the JWT token
        $insertData['name'] = $data['name'] ?? $insertData['email'];
        // Last IPs comes as ipaddr in the JWT token, if it's not present, use the current IP
        $insertData['last_ips'] = currentIP();
        // If JWT has a claim called 'ctry' take it, otherwise take the browser language
        $insertData['origin_country'] = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        // Role comes as an array of roles in the JWT token, we only need the first one
        $insertData['role'] = 'user'; // There are no roles in live id tokens
        // Theme is set to the default color scheme
        $insertData['theme'] = COLOR_SCHEME;
        $insertData['provider'] = 'mslive';
        $insertData['enabled'] = 1;

        $allowedData = ['username', 'email', 'name', 'last_ips', 'origin_country', 'role', 'theme', 'provider', 'enabled'];

        $checks = new Checks($allowedData, $insertData);

        $checks->checkParams($allowedData, $insertData);

        try {
            $createUser = new UserModel();
            $createUser->create($insertData);
            if ($returnResult) {
                return;
            }
        } catch (UserExceptions $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: UserExceptions creating mslive provider user', 'unable to create user');
        } catch (\Exception $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: Exception creating mslive provider user', 'unable to create user');
        }
    }
    public function createGoogleUser(array $data, bool $returnResult = true): void
    {
        // $data is the contents of the JWT token, so we need to do some transformations before we can use it
        $insertData = [];
        // usernames comes as preferred_username in the JWT token
        $insertData['username'] = $data['email'] ?? Response::output('Missing email in token', 400);
        // Prepare the email, if it's not present in the JWT token, use the username
        $insertData['email'] = $data['email'];
        // Name comes as name in the JWT token
        $insertData['name'] = $data['name'] ?? $data['email'];
        // Last IPs comes as ipaddr in the JWT token, if it's not present, use the current IP
        $insertData['last_ips'] = currentIP();
        // If JWT has a claim called 'ctry' take it, otherwise take the browser language
        $insertData['origin_country'] = (isset($data['locale'])) ? $data['locale'] : substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        // Role comes as an array of roles in the JWT token, we only need the first one
        $insertData['role'] = $data['roles'][0] ?? 'user';
        // Theme is set to the default color scheme
        $insertData['theme'] = COLOR_SCHEME;
        $insertData['picture'] = $data['picture'] ?? null;
        $insertData['provider'] = 'google';
        $insertData['enabled'] = 1;

        $allowedData = ['username', 'email', 'name', 'last_ips', 'origin_country', 'role', 'theme', 'provider', 'enabled'];

        $checks = new Checks($allowedData, $insertData);

        $checks->checkParams($allowedData, $insertData);

        try {
            $createUser = new UserModel();
            $createUser->create($insertData);
            if ($returnResult) {
                return;
            }
        } catch (UserExceptions $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: UserExceptions creating google provider user', 'unable to create user');
        } catch (\Exception $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: Exception creating google provider user', 'unable to create user');
        }
    }
    public function createLocalUser(array $data, bool $returnResult = true): void
    {
        if (!isset($data['email'])) {
            $data['email'] = $data['username'];
        }

        $allowedData = ['username', 'password', 'confirm_password', 'email', 'name', 'last_ips', 'origin_country', 'role', 'theme', 'provider', 'enabled'];

        $checks = new Checks($allowedData, $data);

        $checks->checkParams($allowedData, $data);

        if ($data['password'] !== $data['confirm_password']) {
            Response::output('Passwords do not match', 400);
        }

        // Unset the confirm_password and do not hash the password yet, the Model will do it
        unset($data['confirm_password']);

        try {
            $createUser = new UserModel();
            $createUser->create($data);
            if ($returnResult) {
                Response::output('User created');
            }
        } catch (UserExceptions $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: UserExceptions creating local provider user', $e->getMessage());
        } catch (\Exception $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: Exception creating local provider user', $e->getMessage());
        }
    }

    public function update(array $data, int $id): void
    {
        $user = new UserModel();
        try {
            $user->update($data, $id);
            Response::output('User updated');
        } catch (UserExceptions $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: UserExceptions updating user with id ' . $id . ' and data ' . json_encode($data), 'unable to update user');
        } catch (\Exception $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: Exception updating user with id ' . $id . ' and data ' . json_encode($data), 'unable to update user');
        }
    }
    public function delete(int $id): void
    {
        $user = new UserModel();
        $this->deleteProfilePicture($id);
        try {
            if ($user->delete($id)) {
                Response::output('User deleted');
            }
            Response::output('User deleted');
        } catch (UserExceptions $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: UserExceptions deleting user with id ' . $id, 'unable to delete user');
        } catch (\Exception $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: Exception deleting user with id ' . $id, 'unable to delete user');
        }
    }
    public function updateLastLogin(string $username): void
    {
        // First check if the user exists
        $updatedUser = new UserModel();
        $updatedUserArray = $updatedUser->get($username);

        try {
            $updatedUser->update(['last_login' => date('Y-m-d H:i:s')], $updatedUserArray['id']);
        } catch (UserExceptions $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: UserExceptions updating last login for username ' . $username, 'unable to update last login');
        } catch (\Exception $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: Exception updating last login for username ' . $username, 'unable to update last login');
        }
    }
    public function saveAzureProfilePicture(string $username, string $response): void
    {
        $imageName = $username . '_' . uniqid() . '.jpeg';
        $imageRelativePath = '/assets/images/profile/' . $imageName;
        $savePath = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/profile/' . $imageName;

        file_put_contents($savePath, $response);

        $user = new UserModel();

        try {
            $usernameConfirmed = $user->get($username);
        } catch (UserExceptions $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: UserExceptions getting user for saving azure profile picture', 'unable to get username');
        } catch (\Exception $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: Exception getting user for saving azure profile picture', 'unable to get username');
        }

        try {
            $user->update(['picture' => $imageRelativePath], $usernameConfirmed['id']);
        } catch (UserExceptions $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: error saving azure profile picture', 'unable to save profile picture');
        } catch (\Exception $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: error saving azure profile picture', 'unable to save profile picture');
        }
    }
    public function deleteProfilePicture(int $id): void
    {
        $user = new UserModel();

        try {
            $usernameConfirmed = $user->get($id);
        } catch (UserExceptions $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: UserExceptions getting user for deleting profile picture', 'unable to get username');
        } catch (\Exception $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: Exception getting user for deleting profile picture', 'unable to get username');
        }

        $currentPicture = $usernameConfirmed['picture'];
        $profilePicturePath = ROOT . '/public' . $currentPicture;

        if (file_exists($profilePicturePath)) {
            unlink($profilePicturePath);
        }

        try {
            $user->update(['picture' => ''], $usernameConfirmed['id']);
        } catch (UserExceptions $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: UserExceptions deleting profile picture', 'unable to delete profile picture');
        } catch (\Exception $e) {
            $this->handleUserErrorsApiResponse($e, 'user controller: Exception deleting profile picture', 'unable to delete profile picture');
        }
    }
    private function handleUserErrorsApiResponse(\Exception $e, string $verboseError, string $publicError): never
    {
        if (ERROR_VERBOSE) {
            Response::output($verboseError . '. Error: ' . $e->getMessage());
        } else {
            Response::output($publicError, 404);
        }
    }
}
