<?php

declare(strict_types=1);

use Models\BasicModel;
use App\Api\Response;
use App\Api\Checks;

// Skip empty array case
if (empty($_POST)) {
    Response::output('No data provided for creation', 400);
}

$checks = new Checks($loginInfo, $_POST);

$checks->checkParams(['table'], $_POST);

$checks->apiChecks();

$table = $_POST['table'];

// Remove system fields that shouldn't be set manually
unset($_POST['table']);
unset($_POST['csrf_token']);

$db = new \App\Database\DB();

try {
    $preparedData = $db->prepareLocalDataForDB($_POST, $table);
} catch (\Exception $e) {
    Response::output($e->getMessage(), 400);
}

$model = new BasicModel($table);

try {
    $create = $model->create($preparedData);
} catch (\Exception $e) {
    Response::output('Error creating record: ' . $e->getMessage(), 400);
}
