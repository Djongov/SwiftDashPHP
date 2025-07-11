<?php

declare(strict_types=1);

use App\Api\Response;
use App\Api\Checks;
use Components\DataGrid;

$checks = new Checks($loginInfo, $_POST);

$checks->checkParams(['request_id'], $_POST);

$checks->adminCheck();

$requestId = $_POST['request_id'];

try {
    echo DataGrid::fromQuery("api_access_log", "SELECT * FROM api_access_log WHERE request_id = '$requestId'", $requestId, $theme);
} catch (Exception $e) {
    if (ERROR_VERBOSE) {
        Response::output($e->getMessage(), $e->getCode());
    } else {
        Response::output('An error occurred while fetching the API access log', 500);
    }
}
