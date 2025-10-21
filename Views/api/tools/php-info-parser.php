<?php

declare(strict_types=1);

use App\Api\Response;
use App\Api\Checks;
use App\Utilities\General;
use App\Security\Firewall;
use Components\DataGrid;

Firewall::activate();

$checks = new Checks($loginInfo, $_POST);

// Perform the API checks
$checks->apiAdminChecks();

// Awaiting parameters
$allowedParams = ['api-action', 'csrf_token'];

// Check if the required parameters are present
$checks->checkParams($allowedParams, $_POST);

if ($_POST['api-action'] !== 'parse-phpinfo') {
    Response::output('Invalid action', 400);
}

$phpInfoArray = General::parsePhpInfo();
echo '<div class="ml-4 dark:text-gray-400">';
    echo DataGrid::fromData('', $phpInfoArray["Features "], $theme);
echo '</div>';
