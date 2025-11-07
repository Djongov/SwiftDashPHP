<?php

declare(strict_types=1);

use Components\Alerts;
use App\Database\DB;
use App\Security\Firewall;
use App\Api\Response;

// First firewall check
Firewall::activate();

// Admin check
if (!$isAdmin) {
    Response::output('You are not an admin', 403);
}

$dbTables = [];

$db = new DB();
$pdo = $db->getConnection();

// Read and execute queries from the SQL file to create tables. We have a different migrate file for different database drivers
$migrateFile = ROOT . '/.tools/migrate_' . DB_DRIVER . '.sql';
$migrate = file_get_contents($migrateFile);

try {
    // Execute multiple queries
    $pdo->exec($migrate);
    echo Alerts::success('Database migration completed successfully.');
} catch (PDOException $e) {
    echo Alerts::danger('Error in migrate file: ' . $e->getMessage(), 400);
}
