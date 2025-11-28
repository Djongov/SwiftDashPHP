<?php

declare(strict_types=1);

define('ROOT', dirname($_SERVER['DOCUMENT_ROOT']));

if (ini_get('display_errors') == 1) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
    define('ERROR_VERBOSE', true);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE & ~E_WARNING);
    define('ERROR_VERBOSE', false);
}

foreach (array_keys($_ENV + getenv()) as $key) {
    $val = getenv($key);
    if ($val !== false) {
        $_ENV[$key] = trim($val, "\"'");
    }
}

class SystemConfig
{
    private static bool $loaded = false;
    
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }
        
        self::loadEnvironment();
        self::validateSystemRequirements();
        self::defineConstants();
        
        self::$loaded = true;
    }
    
    private static function loadEnvironment(): void
    {
        // First, check if at least one required variable is already in $_ENV
        $requiredEnvConstants = [
            'DB_NAME',
            'DB_DRIVER',
            'LOCAL_LOGIN_ENABLED',
            'GOOGLE_LOGIN_ENABLED',
            'MSLIVE_LOGIN_ENABLED',
            'ENTRA_ID_LOGIN_ENABLED'
        ];

        $anyLoaded = false;
        foreach ($requiredEnvConstants as $var) {
            if (isset($_ENV[$var]) || getenv($var) !== false) {
                $anyLoaded = true;
                break;
            }
        }

        // If none of the critical env vars are set, try loading the .env file
        if (!$anyLoaded) {
            $envPath = ROOT . DIRECTORY_SEPARATOR . '.env';
            if (!file_exists($envPath)) {
                die('The .env file is missing. Either set environment variables or create one in the root of the project. Alternatively, go to /create-env to generate it.');
            }

            $dotenv = \Dotenv\Dotenv::createImmutable(ROOT);
            try {
                $dotenv->safeLoad();
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }
        // Normalize environment values into $_ENV
        foreach ($requiredEnvConstants as $var) {
            $value = getenv($var) ?: ($_ENV[$var] ?? '');
            $_ENV[$var] = trim($value, "\"'");
        }
    }

    
    private static function validateSystemRequirements(): void
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, '8.4.0', '<')) {
            die('PHP 8.4 or higher is required');
        }

        // Core required environment variables
        $requiredEnvConstants = [
            'DB_NAME',
            'DB_DRIVER',
            'LOCAL_LOGIN_ENABLED',
            'GOOGLE_LOGIN_ENABLED',
            'MSLIVE_LOGIN_ENABLED',
            'ENTRA_ID_LOGIN_ENABLED'
        ];

        foreach ($requiredEnvConstants as $constant) {
            if (!isset($_ENV[$constant])) {
                die("$constant must be set in the environment");
            }
        }

        // Now, conditionally validate DB-specific variables only for non-sqlite drivers
        $driver = $_ENV['DB_DRIVER'];
        if ($driver !== 'sqlite') {
            $dbRequired = ['DB_SSL', 'DB_HOST', 'DB_USER', 'DB_PASS', 'DB_PORT'];
            foreach ($dbRequired as $var) {
                if (!isset($_ENV[$var])) {
                    die("$var must be set in the environment when DB_DRIVER is $driver");
                }
            }
        }

        self::validateExtensions();
    }
    
    private static function validateExtensions(): void
    {
        $missingExtensions = [];
        $requiredExtensions = ['curl', 'openssl', 'intl'];
        
        // Add database-specific extensions
        switch ($_ENV['DB_DRIVER']) {
            case 'pgsql':
                $requiredExtensions[] = 'pdo_pgsql';
                break;
            case 'sqlsrv':
                $requiredExtensions[] = 'pdo_sqlsrv';
                break;
            case 'sqlite':
                $requiredExtensions[] = 'pdo_sqlite';
                break;
            case 'mysql':
                $requiredExtensions[] = 'pdo_mysql';
                break;
        }
        
        foreach ($requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                $missingExtensions[] = $extension;
            }
        }
        
        if (!empty($missingExtensions)) {
            die('The following extensions are missing: ' . implode(', ', $missingExtensions));
        }
    }
    
    private static function defineConstants(): void
    {
        // Core constants
        define(
            'SYSTEM_LOGIN_EXEMPTIONS',
            [
                '/api/csp-report',
                '/api/set-lang',
                '/api/cookie-consent',
                '/auth/azure-ad',
                '/auth/google',
                '/auth/local',
                '/auth/azure/azure-ad-code-exchange',
                '/auth/azure/mslive-code-exchange',
                '/api/user',
                '/install',
                '/logout',
            ]
        );
        
        define('VERSION', trim(file_get_contents(ROOT . DIRECTORY_SEPARATOR . 'version.txt')));
        
        // Database constants
        self::defineDatabaseConstants();
        
        // Security constants
        self::defineSecurityConstants();
        
        // Service constants
        self::defineServiceConstants();

        // DB settings from AppSettings table
        self::configureDBSettings();

        // Authentication constants
        self::defineAuthConstants();
    }
    
    private static function defineDatabaseConstants(): void
    {
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
            
            foreach ($dbRelatedConstants as $constant) {
                if (!isset($_ENV[$constant])) {
                    die($constant . ' must be set in the environment when DB_DRIVER is ' . DB_DRIVER);
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

        // SSL certificates
        define("DB_CA_CERT", ROOT . DIRECTORY_SEPARATOR . '.tools' . DIRECTORY_SEPARATOR . 'NewDigiCertGlobalRootCA.crt.pem');
        define("CURL_CERT", ROOT . DIRECTORY_SEPARATOR . '.tools' . DIRECTORY_SEPARATOR . 'cacert.pem');
    }

    
    private static function defineSecurityConstants(): void
    {
        // Security headers for fetch requests
        define('SECRET_HEADER', 'secretheader');
        define('SECRET_HEADER_VALUE', 'badass');
        define('API_KEY_HEADER_NAME', 'X-API-Key');
        
        // Webhook security
        define('WEBHOOK_SECRET_NAME', 'webhook-secret');
        define('WEBHOOK_SECRET', $_ENV['WEBHOOK_SECRET'] ?? '');
    }
    
    private static function defineServiceConstants(): void
    {
        // Sendgrid settings
        define("SENDGRID", $_ENV['SENDGRID_ENABLED'] === 'true' ? true : false);
        
        if (SENDGRID) {
            if (!isset($_ENV['SENDGRID_API_KEY'])) {
                die('SENDGRID_API_KEY must be set in the .env file');
            }
            define("SENDGRID_API_KEY", $_ENV['SENDGRID_API_KEY']);
        }
        
        // TinyMCE settings
        define("TINYMCE", true);
        if (TINYMCE) {
            define("TINYMCE_SCRIPT_LINK", 'https://cdn.tiny.cloud/1/z5zdktmh1l2u1e6mhjuer9yzde2z48kc5ctgg9wsppaobz9s/tinymce/7/tinymce.min.js');
        }
        
        // QuickChart settings
        define("QUICKCHART_HOST", "quickchart.io");
    }
    
    private static function defineAuthConstants(): void
    {
        // Authentication handler configuration
        define('AUTH_HANDLER', 'session'); // cookie/session
        define('JWT_ISSUER', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]");
        define('JWT_TOKEN_EXPIRY', (int) AUTH_EXPIRY);
        define('USE_REMOTE_ID_TOKEN', false);
        define('AUTH_COOKIE_EXPIRY', (int) AUTH_EXPIRY);
        define('SUPPORTED_AUTH_PROVIDERS', ['azure', 'mslive', 'google', 'local']);

        if (AUTH_HANDLER === 'cookie') {
            define('AUTH_COOKIE_NAME', 'auth_cookie');
        } elseif (AUTH_HANDLER === 'session') {
            define('AUTH_SESSION_NAME', 'auth_session');
        } else {
            die('AUTH_HANDLER must be set to cookie or session');
        }

        // Set variables needed by auth config files
        $GLOBALS['destination'] = (isset($_GET['destination'])) ? $_GET['destination'] : $_SERVER['REQUEST_URI'];
        $GLOBALS['protocol'] = (str_contains($_SERVER['HTTP_HOST'], 'localhost')) ? 'http' : 'https';

        // Set up authentication providers
        self::configureLocalAuth();
        self::configureEntraIdAuth();
        self::configureMicrosoftLiveAuth();
        self::configureGoogleAuth();
    }
    
    private static function configureLocalAuth(): void
    {
        define('LOCAL_USER_LOGIN', filter_var($_ENV['LOCAL_LOGIN_ENABLED'], FILTER_VALIDATE_BOOLEAN));
        if (LOCAL_USER_LOGIN) {
            if (!isset($_ENV['JWT_PUBLIC_KEY']) || !isset($_ENV['JWT_PRIVATE_KEY'])) {
                die('JWT_PUBLIC_KEY and JWT_PRIVATE_KEY must be set in the .env file');
            }
            define("JWT_PUBLIC_KEY", $_ENV['JWT_PUBLIC_KEY']);
            define("JWT_PRIVATE_KEY", $_ENV['JWT_PRIVATE_KEY']);
            define('MANUAL_REGISTRATION', true);
        }
    }
    
    private static function configureEntraIdAuth(): void
    {
        define('ENTRA_ID_LOGIN', filter_var($_ENV['ENTRA_ID_LOGIN_ENABLED'], FILTER_VALIDATE_BOOLEAN));
        if (ENTRA_ID_LOGIN) {
            // Extract variables for the included config file
            $destination = $GLOBALS['destination'];
            $protocol = $GLOBALS['protocol'];
            include_once ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'azure-ad-auth-config.php';
        }
    }
    
    private static function configureMicrosoftLiveAuth(): void
    {
        define('MICROSOFT_LIVE_LOGIN', filter_var($_ENV['MSLIVE_LOGIN_ENABLED'], FILTER_VALIDATE_BOOLEAN));
        if (MICROSOFT_LIVE_LOGIN) {
            // Extract variables for the included config file
            $destination = $GLOBALS['destination'];
            $protocol = $GLOBALS['protocol'];
            include_once ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'microsoft-live-auth-config.php';
        }
    }
    
    private static function configureGoogleAuth(): void
    {
        define('GOOGLE_LOGIN', filter_var($_ENV['GOOGLE_LOGIN_ENABLED'], FILTER_VALIDATE_BOOLEAN));
        if (GOOGLE_LOGIN) {
            // Extract variables for the included config file
            $destination = $GLOBALS['destination'];
            $protocol = $GLOBALS['protocol'];
            include_once ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'google-auth-config.php';
        }
    }
    private static function configureDBSettings(): void
    {
        $appSettings = new \Models\AppSettings();
        $allAppSettings = $appSettings->getAllByOwner('system');
        $requiredAppSettings = ['default_data_grid_engine', 'auth_expiry', 'use_tailwind_cdn', 'color_scheme'];
        foreach ($allAppSettings as $setting) {
            if (in_array($setting['name'], $requiredAppSettings)) {
                $constantName = strtoupper($setting['name']);
                define($constantName, $setting['value']);
            }
        }
    }
}

// Initialize the system configuration
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$destination = (isset($_GET['destination'])) ? $_GET['destination'] : $_SERVER['REQUEST_URI'];
SystemConfig::load();
