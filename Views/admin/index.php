<?php

declare(strict_types=1);

use Components\Html;
use Components\Table;
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

// Check the database driver to determine the appropriate SQL syntax
$dbTables = $db->getTableNames();

// $dbTables now contains the table names fetched from the database
echo '<div class="p-4 m-4 max-w-md ' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700 overflow-auto">';
    echo Html::h2('Database Tables', true);
    echo Html::p('Connected to DB: ' . DB_NAME);
    echo Html::p('DB Host: ' . DB_HOST);
    echo Html::p('DB User: ' . DB_USER);
    echo Html::p('DB Driver: ' . $db->getDriver());
    echo Html::p('Using SSL: ' . (DB_SSL ? 'Yes' : 'No'));
    echo Html::p('DB_CERT:' . DB_CA_CERT);
    echo Html::p('Total tables: ' . count($dbTables));
    echo Table::auto($dbTables);
echo '</div>';
