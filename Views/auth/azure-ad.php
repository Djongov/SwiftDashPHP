<?php

declare(strict_types=1);

use App\Api\Response;
use App\Authentication\JWT;
use App\Authentication\Azure\AzureAD;
use Controllers\User;
use Models\User as UserModel;
use App\Authentication\AuthToken;
use App\Logs\SystemLog;

if (isset($_POST['error'], $_POST['error_description'])) {
    Response::output("Azure Error: " . $_POST['error'] . " with Description: " . $_POST['error_description'], 400);
}

if (!isset($_POST['id_token'], $_POST['state'])) {
    Response::output('Invalid request', 400);
}

// If someone comes directly from /login, we need to set the state to /
if ($_POST['state'] === '/login' || $_POST['state'] === '/logout') {
    $_POST['state'] = '/';
}

$idToken = $_POST['id_token'];
$idTokenArray = JWT::parseTokenPayLoad($idToken);

$requiredClaims = ['preferred_username', 'name', 'exp', 'iss'];

foreach ($requiredClaims as $claim) {
    if (!array_key_exists($claim, $idTokenArray)) {
        Response::output('Invalid token claims', 400);
    }
}

// If it is an MSLIVE token, then the issueer wil; be https://login.live.com or https://login.microsoftonline.com/9188040d-6c67-4c5b-b112-36a304b66dad/v2.0
if ($idTokenArray['iss'] === 'https://login.live.com' || $idTokenArray['iss'] === 'https://login.microsoftonline.com/9188040d-6c67-4c5b-b112-36a304b66dad/v2.0') {
    // No check for now
} else {
    // Let's call the function to check the JWT token which is returned. We are checking stuff like expiration, issuer, app id. We also do validation of the token signature
    if (!AzureAD::check($idToken)) {
        Response::output('Invalid token', 400);
    }
}
// instantiate the user class
$user = new User();
$userModel = new UserModel();
// Check if the user exists in the DB
if ($userModel->exists($idTokenArray['preferred_username'])) {
    // User exists, let's update the last login
    $userDetailsArray = $userModel->get($idTokenArray['preferred_username']);
    if ($userDetailsArray['provider'] !== 'azure' && $userDetailsArray['provider'] !== 'mslive') {
        Response::output('User exists but is not an Entra ID or MS Live account', 400);
    }
    //$user->updateLastLogin($idTokenArray['preferred_username']);
    $updateUserLastLoginInfo = $userModel->update(['last_login' => date('Y-m-d H:i', time())], $userDetailsArray['id']);
    if ($updateUserLastLoginInfo !== 1) {
        // We could stop the process here and return an error message or because this is not a critical error, we could just log it and continue
        SystemLog::write('Could not update last login for user: ' . $userDetailsArray['username'] . '. Result of update is ' . gettype($updateUserLastLoginInfo), 'User Update Error');
    } // No need for else as the update function will record this in the logs
} else {
    // User does not exist, let's create it (this will also update the last login)
    if ($idTokenArray['iss'] === 'https://login.live.com') {
        $provider = 'mslive';
    } else {
        $provider = 'azure';
    }
    try {
        $user->create($idTokenArray, $provider);
        SystemLog::write('User created with email: ' . $idTokenArray['preferred_username'] . ' and provider ' . $provider, 'User Creation');
    } catch (Exception $e) {
        SystemLog::write('Could not create user with email: ' . $idTokenArray['preferred_username'] . '. Error: ' . $e->getMessage(), 'User Creation Error');
        Response::output('Could not create user', 500);
    }
}

// Issue a local JWT token if not using remote ID token
if (!USE_REMOTE_ID_TOKEN && LOCAL_USER_LOGIN) {
    $idToken = JWT::generateToken(
        [
        'provider' => 'azure',
        'username' => $idTokenArray['preferred_username'],
        'name' => $idTokenArray['name'],
        'email' => $idTokenArray['email'] ?? null,
        'roles' => [
            $userDetailsArray['role']
        ],
        'last_ip' => currentIP()
        ], JWT_TOKEN_EXPIRY
    );
}
// Let's set the "auth_cookie" and put the id token as it's value, set the expiration date to when the token should expire and the rest of the cookie settings
AuthToken::set($idToken);

$destinationUrl = $_POST['state'] ?? null;
// Valid destination, proceed to redirect to the destination
header("Location: " . filter_var($destinationUrl, FILTER_SANITIZE_URL));
exit();
