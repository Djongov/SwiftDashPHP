<?php

declare(strict_types=1);

define('ROOT', dirname($_SERVER['DOCUMENT_ROOT']));

if (ini_get('display_errors') == 1) {
    error_reporting(E_ALL);
    define('ERROR_VERBOSE', true);
} else {
    error_reporting(0);
    define('ERROR_VERBOSE', false);
}

$version = trim(file_get_contents(ROOT . DIRECTORY_SEPARATOR . 'version.txt'));

define('MULTILINGUAL', true);

// Do a check here if .env file exists
if (!file_exists(ROOT . DIRECTORY_SEPARATOR . '.env')) {
    die('The .env file is missing. Please create one in the root of the project or use the <a href="/create-env">helper</a>');
}

// Load the environment variables from the .env file which resides in the root of the project
$dotenv = \Dotenv\Dotenv::createImmutable(ROOT);

try {
    $dotenv->load();
} catch (\Exception $e) {
    die($e->getMessage());
}

/*

DB Settings

$_ENV is taking values from the .env file in the root of the project. If you are not using .env, hardcode them or pass them as env variables in your server

*/
$requiredEnvConstants = [
    'DB_NAME',
    'DB_DRIVER',
    'LOCAL_LOGIN_ENABLED',
    'GOOGLE_LOGIN_ENABLED',
    'MSLIVE_LOGIN_ENABLED',
    'ENTRA_ID_LOGIN_ENABLED',
    'SENDGRID_ENABLED'
];

foreach ($requiredEnvConstants as $constant) {
    if (!isset($_ENV[$constant])) {
        die($constant . ' must be set in the .env file');
    }
}

define("DB_NAME", $_ENV['DB_NAME']);
define("DB_DRIVER", $_ENV['DB_DRIVER']);

if (DB_DRIVER !== 'sqlite') {
    $dbRelatedConstants = [
        'DB_SSL',
        'DB_HOST',
        'DB_USER',
        'DB_PASS',
        'DB_PORT',
    ];
    $dbRelatedConstants[] = 'DB_PORT';
    foreach ($dbRelatedConstants as $constant) {
        if (!isset($_ENV[$constant])) {
            die($constant . ' must be set in the .env file');
        }
    }
    define("DB_SSL", filter_var($_ENV['DB_SSL'], FILTER_VALIDATE_BOOLEAN));
    define("DB_HOST", $_ENV['DB_HOST']);
    define("DB_USER", $_ENV['DB_USER']);
    define("DB_PASS", $_ENV['DB_PASS']);
    define("DB_PORT", (int) $_ENV['DB_PORT']);
} else {
    // For sqlite, we only need DB_NAME and DB_DRIVER so the rest will be empty
    define("DB_SSL", false);
    define("DB_HOST", '');
    define("DB_USER", '');
    define("DB_PASS", '');
    define("DB_PORT", 0);
}


// This is the DigiCertGlobalRootCA.crt.pem file that is used to verify the SSL connection to the DB. It's located in the .tools folder
define("DB_CA_CERT", ROOT . DIRECTORY_SEPARATOR . '.tools' . DIRECTORY_SEPARATOR . 'DigiCertGlobalRootCA.crt.pem');
// This is used by the curl requests so you don't get SSL verification errors. It's located in the .tools folder
define("CURL_CERT", ROOT . DIRECTORY_SEPARATOR . '.tools' . DIRECTORY_SEPARATOR . 'cacert.pem');

// This needs to be set to what is set across the fetch requests in the javascript files. Default is the below
define('SECRET_HEADER', 'secretheader');
// Same as above
define('SECRET_HEADER_VALUE', 'badass');
// api key name
define('API_KEY_NAME', 'x-api-key');

/*

Mailer Settings (Sendgrid)

*/

define("SENDGRID", filter_var($_ENV['SENDGRID_ENABLED'], FILTER_VALIDATE_BOOLEAN));

if (SENDGRID) {
    if (!isset($_ENV['SENDGRID_API_KEY'])) {
        die('SENDGRID_API_KEY must be set in the .env file');
    }
    define("SENDGRID_API_KEY", $_ENV['SENDGRID_API_KEY']);
    #define("SENDGRID_TEMPLATE_ID", 'd-381e01fdce2b44c48791d7a12683a9c3');
}

define("FROM", 'admin@gamerz-bg.com');
define("FROM_NAME", 'No Reply');

/*

Text Editor Settings (TinyMCE)

*/

define("TINYMCE", true);

if (TINYMCE) {
    define("TINYMCE_SCRIPT_LINK", 'https://cdn.tiny.cloud/1/z5zdktmh1l2u1e6mhjuer9yzde2z48kc5ctgg9wsppaobz9s/tinymce/7/tinymce.min.js');
    #define("TINYMCE_API_KEY", $_ENV['TINYMCE_API_KEY']);
}

/*

Charts

For displaying non-JS charts we utilize Quickchart.io. It's a free service that allows you to generate charts from a simple URL. We use it to generate the charts in the form of images which are suited for emailing them safely or display charts from the backend. However, we introduce QUICKCHART_HOST so you can host your own instance of Quickchart.io and use it instead of the public one. This is useful if you want to keep your data private and not send it to a third party service. If you want to host your own instance, you need an app hosting the docker image of Quickchart.io. You can find it here: ianw/quickchart:latest

*/

define("QUICKCHART_HOST", "quickchart.io");

/*

Authentication Settings

*/

// Name of the authentication cookie which holds the JWT token
define('AUTH_HANDLER', 'session'); // cookie/session
define('JWT_TOKEN_EXPIRY', 3600);
define('AUTH_COOKIE_EXPIRY', 86400); // In case cookie is used for handler, make the duration 1 day. Even if Azure tokens cannot exceed 1 hour, if cookie is present it will redirect on its own to refresh the token, so for best user experience it's good to have a longer duration than the token itself
define('SUPPORTED_AUTH_PROVIDERS', ['azure', 'mslive', 'google', 'local']);

if (AUTH_HANDLER === 'cookie') {
    define('AUTH_COOKIE_NAME', 'auth_cookie');
} elseif (AUTH_HANDLER === 'session') {
    define('AUTH_SESSION_NAME', 'auth_session');
} else {
    die('AUTH_HANDLER must be set to cookie or session');
}

$destination = (isset($_GET['destination'])) ? $_GET['destination'] : $_SERVER['REQUEST_URI'];
$protocol = (str_contains($_SERVER['HTTP_HOST'], 'localhost')) ? 'http' : 'https';

// Whether to allow users to login with local accounts
define('LOCAL_USER_LOGIN', filter_var($_ENV['LOCAL_LOGIN_ENABLED'], FILTER_VALIDATE_BOOLEAN));
if (LOCAL_USER_LOGIN) {
    if (!isset($_ENV['JWT_PUBLIC_KEY']) || !isset($_ENV['JWT_PRIVATE_KEY'])) {
        die('JWT_PUBLIC_KEY and JWT_PRIVATE_KEY must be set in the .env file');
    }
    // This is used by the JWT handler to sign the tokens. It's should be a base64 encoded string of the public key
    define("JWT_PUBLIC_KEY", $_ENV['JWT_PUBLIC_KEY']);
    // This is used by the JWT handler to sign the tokens. It's should to be a base64 encoded string of the private key
    define("JWT_PRIVATE_KEY", $_ENV['JWT_PRIVATE_KEY']);
    // Whether to allow users to manually register
    define('MANUAL_REGISTRATION', true);
}
// Whether to allow users to login with Azure AD accounts
define('ENTRA_ID_LOGIN', filter_var($_ENV['ENTRA_ID_LOGIN_ENABLED'], FILTER_VALIDATE_BOOLEAN));
if (ENTRA_ID_LOGIN) {
    include_once ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'azure-ad-auth-config.php';
}
define('MICROSOFT_LIVE_LOGIN', filter_var($_ENV['MSLIVE_LOGIN_ENABLED'], FILTER_VALIDATE_BOOLEAN));
if (MICROSOFT_LIVE_LOGIN) {
    include_once ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'microsoft-live-auth-config.php';
}
// Google login
define('GOOGLE_LOGIN', filter_var($_ENV['GOOGLE_LOGIN_ENABLED'], FILTER_VALIDATE_BOOLEAN));
if (GOOGLE_LOGIN) {
    include_once ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'google-auth-config.php';
}

// /* App checks */
$missing_extensions = [];

$required_extensions = [
    'curl',
    'openssl',
    'intl'
];

if (DB_DRIVER === 'pgsql') {
    $required_extensions[] = 'pdo_pgsql';
}

if (DB_DRIVER === 'sqlsrv') {
    $required_extensions[] = 'pdo_sqlsrv';
}

if (DB_DRIVER === 'sqlite') {
    $required_extensions[] = 'pdo_sqlite';
}

if (DB_DRIVER === 'mysql') {
    $required_extensions[] = 'pdo_mysql';
}

foreach ($required_extensions as $extension) {
    if (!extension_loaded($extension)) {
        $missing_extensions[] = $extension;
    }
}

if (!empty($missing_extensions)) {
    die('The following extensions are missing: ' . implode(', ', $missing_extensions));
}

// Check if the server is running PHP 8.4 or higher
if (version_compare(PHP_VERSION, '8.4.0', '<')) {
    die('PHP 8.4 or higher is required');
}
