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
        error_log("get-error-file.php: Processed " . count($errorFileArray) . " error lines");
        error_log("get-error-file.php: First few lines: " . json_encode(array_slice($errorFileArray, 0, 3)));
        error_log("get-error-file.php: Data structure sample: " . json_encode($errorFileArray[0] ?? 'empty'));
        $dataGridEngine = DEFAULT_DATA_GRID_ENGINE;
        error_log("get-error-file.php: Using engine: $dataGridEngine, Data count: " . count($errorFileArray));
        $componentClass = "\\Components\\$dataGridEngine";
        echo $componentClass::fromData($file . ' (' . $dataGridEngine . ')', $errorFileArray, $theme);
    } else {
        echo Alerts::danger('File (' . $file . ') does not exist');
    }
} else {
    echo Alerts::danger('File (' . $file . ') not readable');
}
echo '</div>';
