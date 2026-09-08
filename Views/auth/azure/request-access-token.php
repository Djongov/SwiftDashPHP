<?php

declare(strict_types=1);

use App\Api\Response;
use App\Authentication\Azure\CodeExchange;

// Starts the authorization code flow that fetches a Microsoft Graph access token for the logged-in user.
// Reached via a redirect from AccessToken::get() with state (where to return to) and scope.

if (!$loginInfo['loggedIn'] || empty($loginInfo['usernameArray']['username'])) {
    Response::output('You need to be logged in to request an access token', 401);
}

foreach (['state', 'scope'] as $queryString) {
    if (!isset($_GET[$queryString])) {
        Response::output("{$queryString} query string is required", 400);
    }
}

$username = $loginInfo['usernameArray']['username'];
$provider = $loginInfo['usernameArray']['provider'] ?? '';

// Legacy callers may still pass a username; it must be the logged-in user
if (isset($_GET['username']) && $_GET['username'] !== $username) {
    Response::output('Anomaly detected: username in query string does not match the logged in user', 400);
}

if (!in_array($provider, ['azure', 'mslive'], true)) {
    Response::output('Access tokens can only be requested for Entra ID or MS Live users', 400);
}

// Bind this request to the session and remember where to send the user afterwards
$state = CodeExchange::begin($_GET['state']);

header('Location: ' . CodeExchange::authorizeUrl($provider, $username, $_GET['scope'], $state, 'none'));
exit();
