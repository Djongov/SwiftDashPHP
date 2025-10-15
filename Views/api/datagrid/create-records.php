<?php

declare(strict_types=1);

use App\Database\DB;
use App\Api\Response;
use App\Logs\SystemLog;
use App\Api\Checks;

$checks = new Checks($loginInfo, $_POST);

$checks->checkParams(['table'], $_POST);

$checks->apiChecks();

$table = $_POST['table'];

// Remove system fields that shouldn't be set manually
unset($_POST['table']);
unset($_POST['csrf_token']);

// Skip empty array case
if (empty($_POST)) {
    Response::output('No data provided for creation', 400);
}

// Convert data types for database compatibility
foreach ($_POST as $key => &$value) {
    // Convert numeric strings to appropriate types
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

$columns = array_keys($_POST);

$db = new DB();

// Validate that all columns exist in the table
$db->checkDBColumns($columns, $table);

// Build INSERT SQL query
$columnNames = implode(', ', $columns);
$placeholders = implode(', ', array_fill(0, count($columns), '?'));

$sql = "INSERT INTO $table ($columnNames) VALUES ($placeholders)";

$values = array_values($_POST);

try {
    $pdo = $db->getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
    
    $newId = $pdo->lastInsertId();
    
    SystemLog::write('New record created in ' . $table . ' with ID: ' . $newId, 'DataGrid Create');
    Response::output('Successfully created record in ' . $table . ' with ID: ' . $newId);
    
} catch (\PDOException $e) {
    // Log the error for debugging
    SystemLog::write('Database error creating record in ' . $table . ': ' . $e->getMessage(), 'DataGrid Create Error');
    
    // Return user-friendly error message
    if (str_contains($e->getMessage(), 'Duplicate entry')) {
        Response::output('Record with this data already exists', 409);
    } elseif (str_contains($e->getMessage(), 'cannot be null')) {
        Response::output('Required field is missing', 400);
    } else {
        Response::output('Database error occurred while creating record', 500);
    }
}