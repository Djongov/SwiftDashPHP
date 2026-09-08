<?php

declare(strict_types=1);

use App\Api\Response;
use App\Authentication\JWT;
use App\Authentication\Azure\AccessToken;
use App\Authentication\Azure\CodeExchange;

// Entra ID returns the authorization code here (response_mode=query, so a GET that carries our session cookie).
// The resulting access token is stored for the user of the current session only.

if (!$loginInfo['loggedIn'] || empty($loginInfo['usernameArray']['username'])) {
    Response::output('You need to be logged in to request an access token', 401);
}

if (($loginInfo['usernameArray']['provider'] ?? '') !== 'azure') {
    Response::output('Entra ID access tokens are only available for Entra ID users', 400);
}

$username = $loginInfo['usernameArray']['username'];

// The state must be the one we generated for this session, and it can only be used once
$destination = CodeExchange::consume($_GET['state'] ?? null);

if ($destination === null) {
    Response::output('Invalid or expired state, please start again', 400);
}

if (isset($_GET['error'])) {
    $error = (string) $_GET['error'];
    $description = (string) ($_GET['error_description'] ?? '');

    // The silent attempt (prompt=none) needs the user's consent: ask for it interactively
    if (str_contains($error, 'consent_required') || str_contains($error, 'interaction_required') || str_contains($description, 'AADSTS65001')) {
        $state = CodeExchange::begin($destination);
        header('Location: ' . CodeExchange::authorizeUrl('azure', $username, 'https://graph.microsoft.com/user.read', $state, 'consent'));
        exit();
    }

    // The user declined
    if (str_contains($error, 'access_denied')) {
        header('Location: ' . $destination);
        exit();
    }

    Response::output('Entra ID error: ' . $error . ($description !== '' ? ' - ' . $description : ''), 400);
}

if (!isset($_GET['code'])) {
    Response::output('Invalid request', 400);
}

$token = CodeExchange::exchange('azure', (string) $_GET['code']);

if (isset($token['error'], $token['statusCode'])) {
    // AADSTS70008 / AADSTS54005: the code expired or was already used, start over interactively
    if (str_contains($token['error'], 'AADSTS70008') || str_contains($token['error'], 'AADSTS54005')) {
        $state = CodeExchange::begin($destination);
        header('Location: ' . CodeExchange::authorizeUrl('azure', $username, 'https://graph.microsoft.com/user.read', $state, 'consent'));
        exit();
    }
    Response::output('Could not exchange the code for a token: ' . $token['error'], $token['statusCode']);
}

if (!isset($token['access_token'], $token['token_type']) || $token['token_type'] !== 'Bearer') {
    Response::output('Anomaly detected: unexpected token response', 400);
}

// The token must belong to the logged-in user. Not all tokens carry upn, so fall back to the other identity claims.
$claims = JWT::parseTokenPayLoad($token['access_token']);
$usernameFromToken = $claims['upn'] ?? $claims['email'] ?? $claims['preferred_username'] ?? $claims['unique_name'] ?? null;

if ($usernameFromToken === null) {
    Response::output('Anomaly detected: cannot find a suitable claim for username extraction in the token', 400);
}

if (strtolower((string) $usernameFromToken) !== strtolower($username)) {
    Response::output('Anomaly detected: the token does not belong to the logged in user', 400);
}

try {
    AccessToken::save($token['access_token'], $username);
} catch (Exception $e) {
    Response::output($e->getMessage(), 400);
}

header('Location: ' . $destination);
exit();
