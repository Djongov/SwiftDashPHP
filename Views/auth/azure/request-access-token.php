<?php declare(strict_types=1);

use App\Authentication\JWT;
use App\Authentication\AuthToken;
use App\Api\Response;

$state = $_GET['state'] ?? '/';

$username = JWT::extractUserName(AuthToken::get()) ?? die('No username found');

// if (!isset($_GET['provider'])) {
//     Response::output('No provider specified');
// }

// if ($_GET['provider'] === 'mslive') {
//     $url = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?';
// } elseif ($_GET['provider'] === 'azure') {
//     $url = ENTRA_ID_OAUTH_URL;
// } else {
//     Response::output('Invalid provider');
// }

$data = [
    'client_id' => ENTRA_ID_CLIENT_ID,
    'response_type' => 'code',
    'redirect_uri' => ENTRA_ID_CODE_REDIRECT_URI,
    'scope' => 'https://graph.microsoft.com/user.read',
    'response_mode' => 'form_post',
    'state' => $state . '&username=' . $username,
    //'nonce' => $_SESSION['nonce'],
    'prompt' => 'none',
    'login_hint' => $username
];

if ($usernameArray['provider'] === 'azure') {
    $data = [
        'client_id' => ENTRA_ID_CLIENT_ID,
        'response_type' => 'code',
        'redirect_uri' => ENTRA_ID_CODE_REDIRECT_URI,
        'scope' => 'https://graph.microsoft.com/user.read',
        'response_mode' => 'form_post',
        'state' => $state . '&username=' . $username,
        //'nonce' => $_SESSION['nonce'],
        'prompt' => 'none',
        'login_hint' => $username
    ];
    $url = ENTRA_ID_OAUTH_URL;
} elseif ($usernameArray['provider'] === 'mslive') {
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