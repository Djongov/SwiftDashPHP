<?php

declare(strict_types=1);

use App\Authentication\JWT;
use App\Authentication\AuthToken;
use App\Api\Response;

$requiredQueryStrings = ['state', 'username', 'scope'];

foreach ($requiredQueryStrings as $queryString) {
    if (!isset($_GET[$queryString])) {
        Response::output("{$queryString} query string is required", 400);
    }
}

$username = $_GET['username'];
$scope = $_GET['scope'];
$state = $_GET['state'];

// Let's compare if the username being passed in the query string is the same as the one in the JWT

if ($username !== JWT::extractUserName(AuthToken::get())) {
    Response::output('Anomaly detected: username in query string does not match the one in the JWT', 400);
}

// We can also check the session for the username
if ($loginInfo['usernameArray']['username'] !== $username) {
    Response::output('Anomaly detected: username in session does not match the one in the query string', 400);
}

// Prepare the data for requesting an access token
if ($loginInfo['usernameArray']['provider'] === 'azure') {
    $data = [
        'client_id' => ENTRA_ID_CLIENT_ID,
        'response_type' => 'code',
        'redirect_uri' => ENTRA_ID_CODE_REDIRECT_URI,
        'scope' => $scope,
        'response_mode' => 'form_post',
        'state' => $state . '&username=' . $username,
        //'nonce' => $_SESSION['nonce'],
        'prompt' => 'none',
        'login_hint' => $username
    ];
    $url = ENTRA_ID_OAUTH_URL;
} elseif ($loginInfo['usernameArray']['provider'] === 'mslive') {
    $data = [
        'client_id' => MS_LIVE_CLIENT_ID,
        'response_type' => 'code',
        'redirect_uri' => MS_LIVE_CODE_REDIRECT_URI,
        'scope' => 'https://graph.microsoft.com/user.read',
        'response_mode' => 'form_post',
        'state' => $state . '&username=' . $username,
        //'nonce' => $_SESSION['nonce'],
        'prompt' => 'none',
        'login_hint' => $username
    ];
    $url = 'https://login.microsoftonline.com/consumers/oauth2/v2.0/authorize?';
} else {
    Response::output('Invalid provider');
}

$location = $url . http_build_query($data);

header('Location: ' . $location);

exit();
