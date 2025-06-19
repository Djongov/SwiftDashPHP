<?php

declare (strict_types=1);

use Components\DataGrid;
use App\Security\Firewall;
use App\Database\DB;
use App\Api\Response;
use Components\Html;

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

echo '<form method="get" action="" class="my-8 max-w-md mx-auto p-4 text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-900 rounded-xl shadow-md space-y-4">';
    echo '<div class="flex flex-col">';
        echo '<label for="table" class="mb-1 text-sm font-medium">Select Table:</label>';
        echo '<select name="table" class="' . Html::selectInputClasses($theme) . '">';
            echo '<option value="">-- Select a table --</option>';
            foreach ($dbTables as $table) {
                $selected = (isset($_GET['table']) && $_GET['table'] === $table) ? ' selected' : '';
                echo "<option value=\"{$table}\"{$selected}>{$table}</option>";
            }
        echo '</select>';
    echo '</div>';
    
    echo '<div>';
        echo Html::submitButton('view-table-submit', 'View Table', $theme);
    echo '</div>';
echo '</form>';

if (isset($_GET['table']) && in_array($_GET['table'], $dbTables)) {
    $tableName = $_GET['table'];
    
    echo DataGrid::fromDBTable($tableName, $tableName, $theme);
}
