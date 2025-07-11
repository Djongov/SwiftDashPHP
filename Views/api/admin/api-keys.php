<?php

declare(strict_types=1);

use App\Api\Response;
use App\Api\Checks;

$checks = new Checks($loginInfo, $_POST);

$checks->checkParams(['access', 'note'], $_POST);

$allowedAccess = ['read', 'write'];

if (!in_array($_POST['access'], $allowedAccess, true)) {
    Response::output('Invalid access level provided. Allowed values are: ' . implode(', ', $allowedAccess), 400);
}

if ($_POST['access'] === 'write') {
    $checks->adminCheck();
    $executionLimit = 30;
} else {
    $executionLimit = 10;
}

$api = new Models\APIKeys();

$access = $_POST['access'];
$note = htmlspecialchars($_POST['note'], ENT_QUOTES, 'UTF-8');

try {
    $apiKey = $api->create($access, $note, $loginInfo['usernameArray']['username'], $executionLimit);
    Response::output($apiKey);
} catch (Exception $e) {
    if (ERROR_VERBOSE) {
        Response::output($e->getMessage(), $e->getCode());
    } else {
        Response::output('An error occurred while creating the API key', 500);
    }
}
