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

// Get column information to check which columns allow NULL
$columnInfo = $db->describe($table);

$updates = [];
$values = [];

// Check if all keys in $_POST match the columns
foreach ($_POST as $key => $value) {
    if ($value === null) {
        // Only set to NULL if the column allows null values
        if (isset($columnInfo[$key]) && $columnInfo[$key]['nullable']) {
            $updates[] = "$key = NULL";
        }
        // If column doesn't allow null, skip updating it (keep existing value)
    } else {
        $updates[] = "$key = ?";
        $values[] = $value; // Only add non-null values to the values array
    }
}
// Combine the SET clauses with commas
$sql .= implode(', ', $updates);

// Add a WHERE clause to specify which organization to update
$sql .= " WHERE id = ?";

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
