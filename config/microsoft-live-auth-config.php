<?php declare(strict_types=1);

if (MICROSOFT_LIVE_LOGIN) {
    // Azure App registration tenant id
    define('MS_LIVE_TENANT_ID', $_ENV['MS_LIVE_TENANT_ID']);
    // Azure App registratin client id
    define('MS_LIVE_CLIENT_ID', $_ENV['MS_LIVE_CLIENT_ID']);
    // Azure App registration client secret
    define('MS_LIVE_CLIENT_SECRET', $_ENV['MS_LIVE_CLIENT_SECRET']);
    // Set the protocol to http:// if hostname contains localhost
    // This is how we form the redirect URL. Note that https:// is hardcoded, which is fine as app registrations do not allow for http:// unless it's http://localhost.
    define('MS_LIVE_REDIRECT_URI', $protocol . '://' . $_SERVER['HTTP_HOST'] . '/auth/azure-ad');
    // Let's build the oauth URL which includes the tenant. This is where we will be sending the request to login
    //define('OAUTHURL', 'https://login.microsoftonline.com/organizations/oauth2/v2.0/authorize?');
    define('MS_LIVE_OAUTH_URL', 'https://login.live.com/oauth20_authorize.srf?');
    
    define('MS_LIVE_CODE_REDIRECT_URI', $protocol . '://' . $_SERVER['HTTP_HOST'] . '/auth/azure/mslive-code-exchange');
    define('MS_LIVE_TOKEN_URL', 'https://login.microsoftonline.com/' . MS_LIVE_TENANT_ID . '/oauth2/v2.0/token');

    $authenticationData = [
        'client_id' => MS_LIVE_CLIENT_ID,
        'response_type' => 'id_token',
        'redirect_uri' => MS_LIVE_REDIRECT_URI,
        'response_mode' => 'form_post',
        'scope' => 'openid profile email',
        // Note that the nonce is supposed to be checked on return but you need special settings to keep it somewhere, like in a database. This is why we currently use a static nonce but i leave here a line with random nonce
        'nonce' => $_SESSION['nonce'] ?? null,
        //'nonce' => 'c0ca2663770b3c9571ca843c7106851816e2d415e77369a1',
        'state' => $destination
    ];
    // This basically merges OAUTH URL and $data
    $ms_live_request_token_url = MS_LIVE_OAUTH_URL . http_build_query($authenticationData);
    // Let's form what the login url will be
    define('MS_LIVE_LOGIN_BUTTON_URL', $ms_live_request_token_url);
    // For this one, the logout will be our own script
    define('MS_LIVE_LOGOUT_BUTTON_URL', 'https://login.microsoftonline.com/common/oauth2/v2.0/logout?post_logout_redirect_uri=https://' . $_SERVER['HTTP_HOST']);
}
