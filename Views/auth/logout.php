<?php

declare(strict_types=1);

// If we end up here, then we are relying on our own app registration and the auth JWT token is in the "auth_cookie" so if we want to really log out, we need to clear the cookie.
use App\Authentication\JWT;

JWT::handleValidationFailure();

if (!isset($usernameArray['provider']) || empty($usernameArray['provider'])) {
    header('Location: /');
    exit;
}
// Redirect to root page if provider is local
if ($usernameArray['provider'] === 'local') {
    header('Location: /');
    exit;
}
if ($usernameArray['provider'] === 'google') {
    header('Location: /');
    exit;
}
// Send to AzureAD logout URL if provider is AzureAD
if ($usernameArray['provider'] === 'azure') {
    header('Location: ' . ENTRA_ID_LOGOUT_BUTTON_URL);
    exit;
}
// MS live now
if ($usernameArray['provider'] === 'mslive') {
    header('Location: ' . MS_LIVE_LOGOUT_BUTTON_URL);
    exit;
}
