<?php

declare(strict_types=1);

use App\Authentication\Google;
use App\Api\Response;
use Google\Client;
use Controllers\User;
use Models\User as UserModel;
use App\Authentication\JWT;
use App\Authentication\AuthToken;

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    return;
}

if (isset($_GET['error'], $_GET['state'])) {
    Response::output('Google Error: ' . $_GET['error'], 200);
}

if (!isset($_GET['code'])) {
    Response::output('Invalid request', 400);
}

$state = $_GET['state'];
//$nonce = $_GET['nonce'];
$code = $_GET['code'];

$client = new Client();

$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/auth/google');
$client->addScope("email");
$client->addScope("profile");
$client->addScope("openid");
$client->setPrompt('select_account consent');
$client->setAccessType('offline');
// Set the state too
$client->setState($destination);
// Set nonce
$client->setLoginHint($google_nonce);
$client->setHttpClient(new \GuzzleHttp\Client(['verify' => CURL_CERT, 'timeout' => 60, 'http_errors' => false]));
// Exchange the code for an access token
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error']) && !isset($token['access_token'])) {
    Response::output('Google Error: ' . $token['error_description'], 200);
}

$accessToken = $client->getAccessToken();

$idToken = $accessToken['id_token'];

// We are not going to use ['access_token'] as we are only using this for authentication

// verify the id token
if (!Google::verifyIdToken($idToken)) {
    Response::output('Invalid token', 400);
}

$idTokenArray = JWT::parseTokenPayLoad($idToken);

$user = new User();

$userModel = new UserModel();

if ($userModel->exists($idTokenArray['email'])) {
    $userDetailsArray = $userModel->get($idTokenArray['email']);
    if ($userDetailsArray['provider'] !== 'google') {
        Response::output('User exists but is not a google account', 400);
    }
    // User exists, let's update the last login
    $user->updateLastLogin($idTokenArray['email']);
} else {
    // User does not exist, let's create it (this will also update the last login)
    $user->create($idTokenArray, 'google', false);
}

// Issue a local JWT token if not using remote ID token
if (!USE_REMOTE_ID_TOKEN && LOCAL_USER_LOGIN) {
    $idToken = JWT::generateToken([
        'provider' => 'google',
        'username' => $idTokenArray['email'],
        'name' => $idTokenArray['name'],
        'email' => $idTokenArray['email'],
        'roles' => [
            $userDetailsArray['role']
        ],
        'last_ip' => currentIP()
    ], JWT_TOKEN_EXPIRY);
}

AuthToken::set($idToken);

if (str_contains($state, 'login') || str_contains($state, 'logout')) {
    // Invalid destination or state, set a default state
    $state = '/';
}

// Parse the URL
$parsedState = parse_url($state);

// Extract the path and query components
$path = isset($parsedState['path']) ? $parsedState['path'] : '';
$query = isset($parsedState['query']) ? '?' . $parsedState['query'] : '';

// Combine the path and query string
$pathAndQuery = $path . $query;

header("Location: " . $pathAndQuery);

exit();

/* Instead of using the oauth client, we will get the data from the token */
/*
$client->setAccessToken($token['access_token']);

$oauth2 = new \Google\Service\Oauth2($client);

$userInfo = $oauth2->userinfo->get();

$name = $userInfo->name;

$profileImage = $userInfo->picture;

$email = $userInfo->email;

$country = $userInfo->locale;

$verifiedEmail = $userInfo->verified_email;


$accessToken = $client->getAccessToken();
$user = new User();

if ($user->existByUsername($email)) {
    // User exists, let's update the last login
    $user->recordLastLogin($email);
} else {
    // User does not exist, let's create it (this will also update the last login)
    $user->createGoogleUser();
}
*/
