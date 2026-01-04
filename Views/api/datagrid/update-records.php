<?php

declare(strict_types=1);

use Models\BasicModel;
#use App\Database\DB;
use App\Api\Response;
use App\Logs\SystemLog;
use App\Api\Checks;

$checks = new Checks($loginInfo, $_POST);

$checks->checkParams(['table', 'id'], $_POST);

$checks->apiChecks();

$table = $_POST['table'];

$id = (int) $_POST['id'];

unset($_POST['id']);
unset($_POST['table']);
unset($_POST['last_updated']); // last_updated is handled automatically by the DB or updated_at
unset($_POST['updated_at']); // updated_at is handled automatically by the DB
unset($_POST['created_at']); // created_at should not be updated
unset($_POST['csrf_token']); // CSRF token is not part of the data

// Use the DB class to prepare local data for database insertion
// This will validate columns exist, check nullability, and convert types appropriately
$db = new \App\Database\DB();
try {
    $preparedData = $db->prepareLocalDataForDB($_POST, $table);
} catch (\Exception $e) {
    Response::output($e->getMessage(), 400);
}

$model = new BasicModel($table);

$update = $model->update($preparedData, $id);

if ($update === 1) {
    Response::output('Record updated successfully', 200);
} else {
    Response::output('No changes were made to the record', 400);
}
