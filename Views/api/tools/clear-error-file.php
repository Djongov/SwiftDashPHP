<?php

declare(strict_types=1);

use App\Api\Response;
use App\Api\Checks;
use App\Security\Firewall;
use Components\Alerts;

Firewall::activate();

$checks = new Checks($loginInfo, $_POST);

// Perform the API checks
$checks->apiAdminChecks();

// Awaiting parameters
$allowedParams = ['api-action', 'csrf_token'];

// Check if the required parameters are present
$checks->checkParams($allowedParams, $_POST);

if ($_POST['api-action'] !== 'clear-error-file') {
    Response::output('Invalid action');
}

$file = ini_get('error_log');
if (is_writable($file)) {
    file_put_contents($file, '');
} else {
    echo Alerts::danger('File (' . $file . ') not editable');
}
