<?php

declare(strict_types=1);

use Components\Html;
use Components\Alerts;
use App\Api\Response;
use App\Api\Checks;
use App\Utilities\General;
use App\Security\Firewall;
use Components\DataGrid;

Firewall::activate();

$checks = new Checks($loginInfo, $_POST);

// Perform the API checks
$checks->apiAdminChecksNoJWT();

// Awaiting parameters
$allowedParams = ['api-action', 'csrf_token'];

// Check if the required parameters are present
$checks->checkParams($allowedParams, $_POST);

if ($_POST['api-action'] !== 'get-error-file') {
    Response::output('Invalid action');
}

echo '<div class="ml-4 dark:text-gray-400">';

$file = ini_get('error_log');
if (is_readable($file)) {
    if (is_file($file)) {
        if (empty(file($file))) {
            echo Alerts::danger('File (' . $file . ') is empty');
            return;
        }
        $errorFileArray = [];
        $f = file($file);
        $f = implode(PHP_EOL, $f);
        $f = explode(PHP_EOL . '[', $f);
        foreach ($f as $line) {
            if ($line === "") {
                continue;
            }
            // </div>';
            array_push($errorFileArray, $line);
        }
        $errorFileArray = General::assocToIndexed($errorFileArray);
        echo DataGrid::fromData($file, $errorFileArray, $theme);
    } else {
        echo Alerts::danger('File (' . $file . ') does not exist');
    }
} else {
    echo Alerts::danger('File (' . $file . ') not readable');
}
echo '</div>';
