<?php

declare(strict_types=1);

use App\Database\DB;
use App\Api\Response;
use App\Logs\SystemLog;
use App\Api\Checks;

$checks = new Checks($loginInfo, $_POST);

$checks->checkParams(['table', 'id'], $_POST);

$checks->apiChecks();

$table = $_POST['table'];

$id = $_POST['id'];

unset($_POST['id']);

// Because the POST data comes from a fetch request, it serializes the data and everything comes through as a string which could lead to DB query errors. Let's convert the data to the correct types
foreach ($_POST as $key => &$value) {
    // Convert numeric strings to floats if they contain a decimal point
    if (is_numeric($value)) {
        if (strpos($value, '.') !== false) {
            // Convert to float if there's a decimal point
            $value = floatval($value);
        } else {
            // Otherwise, convert to integer
            $value = intval($value);
        }
    // Convert empty strings to null
    } elseif ($value === '') {
        $value = null;
    }
}

$sql = 'UPDATE ' . $_POST['table'] . ' SET ';

unset($_POST['csrf_token']);
unset($_POST['table']);

$columns = array_keys($_POST);

$db = new DB();

$db->checkDBColumns($columns, $table);

$updates = [];

// Check if all keys in $_POST match the columns
foreach ($_POST as $key => $value) {
    if ($value === '') {
        // Set to NULL or a default value as needed
        $updates[] = "$key = NULL"; // or "$key = DEFAULT"
    } else {
        $updates[] = "$key = ?";
    }
}
// Combine the SET clauses with commas
$sql .= implode(', ', $updates);

// Add a WHERE clause to specify which organization to update
$sql .= " WHERE id = ?";

// Prepare and execute the query using queryPrepared
$values = array_values($_POST);

unset($values['id']);

$values[] = $id; // Add the 'id' for the WHERE clause

$pdo = $db->getConnection();

$stmt = $pdo->prepare($sql);

$stmt->execute($values);

if ($stmt->rowCount() === 0) {
    Response::output('Nothing updated', 409);
} else {
    SystemLog::write('Record id ' . $id . ' edited in ' . $table, 'DataGrid Edit');
    Response::output('successfully edited ' . $stmt->rowCount() . ' records in ' . $table . '');
}
