<?php

declare(strict_types=1);

/**
 * Bootstrap file for CLI scripts
 * This loads essential configuration without initializing web-specific components
 */

// Prevent web access to this file
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line');
}

// Path to the composer autoload file (ROOT will be defined by system-settings.php)
$autoloadPath = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die('Composer autoload file not found at: ' . $autoloadPath . "\n" .
        'Run "composer install" first.' . "\n");
}

// Set DOCUMENT_ROOT for CLI context (needed by system-settings.php)
if (!isset($_SERVER['DOCUMENT_ROOT']) || $_SERVER['DOCUMENT_ROOT'] === '') {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__ . DIRECTORY_SEPARATOR . 'public';
}

// Load configuration files
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/system-settings.php';
require_once __DIR__ . '/config/site-settings.php';

// Set timezone
date_default_timezone_set('UTC');

// Initialize minimal $_SERVER globals for CLI context (DOCUMENT_ROOT already set above)
$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/cli';
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Initialize session arrays if they don't exist
if (!isset($_SESSION)) {
    $_SESSION = [];
}
