<?php declare(strict_types=1);

// Define the start time of the request, it can be used to calculate the time it took to process the request later
define("START_TIME", microtime(true));

// Path to the composer autoload file
$path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (file_exists($path)) {
    require_once $path;
} else {
    die('<b>' . $path . '</b> file not found. You need to run <b>composer update</b>');
}

use App\App;

// Initialize the app
$app = new App();

// Run the app
$app->init();
