<?php

declare(strict_types=1);

use Controllers\User;
use App\Api\Response;
use App\Exceptions\UserExceptions;
use App\Api\Checks;
use App\Logs\SystemLog;
use App\Authentication\JWT;
use App\Authentication\AuthToken;

// Local accounts must not be able to sign in when local login is disabled, even if they exist in the users table
if (!LOCAL_USER_LOGIN) {
    Response::output('Local login is disabled', 403);
}

// If the request is coming from local login, we should have a $_POST['username'] and a $_POST['password'] parameter
if (isset($_POST['username'], $_POST['password'], $_POST['csrf_token'])) {
    // First check the CSRF token
    $checks = new Checks($loginInfo, $_POST);

    $checks->checkCSRF($_POST['csrf_token']);

    // You can implement a sleep here, to slow down the response to and therefore slow down potential spam on the login form
    sleep(0);

    $user = new User();

    try {
        $userArray = $user->get($_POST['username']);
    } catch (UserExceptions $e) {
        Response::output($e->getMessage());
    } catch (\Exception $e) {
        SystemLog::write('Generic error when trying to get local user ' . $_POST['username'] . ' with error: ' . $e->getMessage(), 'User API');
        Response::output('error', 400);
    }

    if (empty($userArray)) {
        Response::output('Invalid username or password', 404); // Do not say if the user exists or not to reduce the risk of enumeration attacks
    }

    // enabled comes back as int, string or bool depending on the driver, so normalise before comparing
    if (isset($userArray['enabled']) && !filter_var($userArray['enabled'], FILTER_VALIDATE_BOOLEAN)) {
        Response::output('User is disabled', 401);
    }

    if (!password_verify($_POST['password'], $userArray['password'])) {
        Response::output('Invalid username or password', 404);
    }

    // By now we assume the user is valid, so let's generate a JWT token
    $jwtExpiry = JWT_TOKEN_EXPIRY;
    $cookieExpiry = AUTH_COOKIE_EXPIRY;
    
    // Check if "Remember Me" was selected
    if (isset($_POST['remember']) && $_POST['remember'] === '1') {
        // Extend both JWT and cookie/session expiry to 1 year
        // This works for both cookie-based and session-based authentication:
        // - Cookie mode: extends the auth cookie lifetime
        // - Session mode: extends the session cookie lifetime
        $oneYear = 365 * 24 * 60 * 60; // 1 year in seconds
        $jwtExpiry = JWT_TOKEN_EXPIRY + $oneYear;
        $cookieExpiry = AUTH_COOKIE_EXPIRY + $oneYear;
    }
    
    $idToken = JWT::generateToken(
        [
        'provider' => 'local',
        'username' => $userArray['username'],
        'name' => $userArray['name'],
        'roles' => [
            $userArray['role'],
        ],
        'last_ip' => currentIP()
        ], $jwtExpiry
    );

    // Set the auth token with the appropriate expiry duration
    AuthToken::set($idToken, $cookieExpiry);
    // Record last login
    $user->updateLastLogin($userArray['username']);

    // Only local paths are accepted as a destination, anything else lands on /
    $destinationUrl = \App\Utilities\General::safeRedirectPath($_POST['state'] ?? null);
    if (in_array($destinationUrl, ['/login', '/logout'], true)) {
        $destinationUrl = '/';
    }
    header("Location: " . $destinationUrl);
    exit();
}
