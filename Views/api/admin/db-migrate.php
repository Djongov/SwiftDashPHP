<?php

declare(strict_types=1);

use App\Api\Response;
use App\Api\Checks;
use App\Security\Firewall;
use App\Database\DB;

Firewall::activate();

$checks = new Checks($loginInfo, $_POST);

$expectedParams = ['migrate_type', 'csrf_token'];

$checks->checkParams($expectedParams, $_POST);

$checks->adminCheck();

$db = new DB();
$pdo = $db->getConnection();

$expectedMigrateTypes = ['system', 'project'];

if (!in_array($_POST['migrate_type'], $expectedMigrateTypes, true)) {
    Response::output('Invalid migrate type', 400);
}

// Read and execute queries from the SQL file to create tables. We have a different migrate file for different database drivers
$migrateFile = ROOT . '/.tools/migrate/' . $_POST['migrate_type'] . '/migrate_' . DB_DRIVER . '.sql';
$migrate = file_get_contents($migrateFile);

try {
    // Execute multiple queries
    $pdo->exec($migrate);
    echo Response::output('Database ' . $_POST['migrate_type'] . ' migration completed successfully.');
} catch (PDOException $e) {
    echo Response::output('Error in migrate ' . $_POST['migrate_type'] . ' file (' . $migrateFile . '): ' . $e->getMessage(), 400);
}

Response::output('unknown action', 400);
