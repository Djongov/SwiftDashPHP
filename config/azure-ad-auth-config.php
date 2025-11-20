<?php

declare(strict_types=1);
if (ENTRA_ID_LOGIN) {
    if (!isset($_ENV['ENTRA_ID_CLIENT_ID']) || !isset($_ENV['ENTRA_ID_TENANT_ID'])) {
        die('ENTRA_ID_CLIENT_ID, ENTRA_ID_TENANT_ID, must be set in the .env file if ENTRA_ID_LOGIN is set to true');
    }

    define('ENTRA_ID_CLIENT_ID', $_ENV['ENTRA_ID_CLIENT_ID']);
    define('ENTRA_ID_TENANT_ID', $_ENV['ENTRA_ID_TENANT_ID']);
    define('ENTRA_ID_CLIENT_SECRET', $_ENV['ENTRA_ID_CLIENT_SECRET'] ?? die('Check you if you have set the ENTRA_ID_CLIENT_SECRET'));

    define('ENTRA_ID_MULTITENANT', false); // Set to true if you want to allow users from any tenant to login

    if (ENTRA_ID_MULTITENANT) {
        define('ENTRA_ID_OAUTH_URL', 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?');
        define('ENTRA_ID_TOKEN_URL', 'https://login.microsoftonline.com/common/oauth2/v2.0/token');
    } else {
        define('ENTRA_ID_OAUTH_URL', 'https://login.microsoftonline.com/' . ENTRA_ID_TENANT_ID . '/oauth2/v2.0/authorize?');
        define('ENTRA_ID_TOKEN_URL', 'https://login.microsoftonline.com/' . ENTRA_ID_TENANT_ID . '/oauth2/v2.0/token');
    }

    /* ID Token */

    define('ENTRA_ID_ID_TOKEN_REDIRECT_URI', $protocol . '://' . $_SERVER['HTTP_HOST'] . '/auth/azure-ad');

    $nonce = (isset($_SESSION['nonce'])) ? $_SESSION['nonce'] : bin2hex(random_bytes(16));
    
    $idTokenData = [
        'client_id' => ENTRA_ID_CLIENT_ID,
        'response_type' => 'id_token',
        'redirect_uri' => ENTRA_ID_ID_TOKEN_REDIRECT_URI,
        'response_mode' => 'form_post',
        'scope' => 'openid profile email',
        'nonce' => $nonce,
        'state' => $destination
    ];

    define('ENTRA_ID_LOGIN_BUTTON_URL', ENTRA_ID_OAUTH_URL . http_build_query($idTokenData));

    /* Code exchange */

    define('ENTRA_ID_CODE_REDIRECT_URI', $protocol . '://' . $_SERVER['HTTP_HOST'] . '/auth/azure/azure-ad-code-exchange');

    /* Access Token */

    //define('ENTRA_ID_ACCESS_TOKEN_REDIRECT_URI', $protocol . '://' . $_SERVER['HTTP_HOST'] . '/auth/azure/receive-access-token');

    /* Logout */

    define('ENTRA_ID_LOGOUT_BUTTON_URL', 'https://login.microsoftonline.com/' . ENTRA_ID_TENANT_ID . '/oauth2/v2.0/logout?post_logout_redirect_uri=' . $protocol . '://' . $_SERVER['HTTP_HOST']);
}
