<?php

declare(strict_types=1);

use App\Database\DB;
use Components\Alerts;
use Components\DataGrid;
use App\Api\Checks;
use App\Security\Firewall;

Firewall::activate();

$checks = new Checks($loginInfo, $_POST);

// Perform the API checks
$checks->apiAdminChecks();

// Awaiting parameters
$allowedParams = ['query', 'csrf_token'];

// Check if the required parameters are present
$checks->checkParams($allowedParams, $_POST);

$query = $_POST['query'];

if (str_contains($query, 'DROP') || str_contains($query, 'TRUNCATE')) {
    echo Alerts::danger('You cannot execute DROP or TRUNCATE queries');
    return;
}

$db = new DB();

$pdo = $db->getConnection();

try {
    $stmt = $pdo->prepare($query);
} catch (\PDOException $e) {
    echo Alerts::danger('Error preparing query: ' . $e->getMessage());
    return;
}

// In this particular situation we will be catching the exception because we want to display the error message
try {
    $stmt->execute();
} catch (\PDOException $e) {
    echo Alerts::danger('Error executing query: ' . $e->getMessage());
    return;
}

$data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
$db->__destruct();

if (str_starts_with($query, 'SELECT')) {
    if (empty($data)) {
        echo Alerts::danger('No data found for this SELECT query');
        return;
    }
    echo '<div class="mx-4">';
        // Capture the table from the query
        echo DataGrid::fromData('Custom Query', $data, $theme);
    echo '</div>';
} elseif (str_starts_with($query, 'DESCRIBE') || str_starts_with($query, 'SHOW')) {
    if (empty($data)) {
        echo Alerts::danger('No data found for this DESCRIBE or SHOW query');
        return;
    } else {
        echo '<div class="mx-4">';
            echo DataGrid::fromData('Custom Query', $data, $theme);
        echo '</div>';
    }
} else {
    $affectedRows = $stmt->rowCount();
    if ($affectedRows === 0) {
        echo Alerts::warning('Query executed successfully, but no rows were affected');
    } else {
        echo Alerts::success('Query executed successfully. ' . $affectedRows . ' rows affected');
    }
}
