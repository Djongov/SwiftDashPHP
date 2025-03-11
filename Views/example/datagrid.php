<?php declare(strict_types=1);

use App\Database\DB;
use Components\Html;
use Components\DataGrid;

echo Html::h1('DataGrid', true);

echo Html::p('DataGrid is a special class that can be used to display data in a table format with a filterable and paginated table (datagrid) with just a few lines of code. It is a very powerful tool that can be used to display data from the database, from an API, or a PHP array. Provides a edit/delete buttons and export to csv and tsv.');

// Exampple 1: From DB Table

echo Html::h2('Example 1: Displaying data from a MySQL table');

echo Html::p('This example will display the data from the users table in the database. There are switches for Editing or Deleting the data. This is only available for database related DataGrids. The table is filterable and paginated.');

echo DataGrid::fromDBTable('users', 'Users', $theme);

echo Html::horizontalLine();

// Example 2: From Query

echo Html::h2('Example 2: Displaying data from a MySQL query');

$query = "SELECT id, username, theme FROM users";

echo Html::p('This example will render data from a custom query - ' . Html::code($query));

echo Html::divBox(DataGrid::fromQuery('users', $query, 'Custom query', $theme, true, false, [
    'filters' => true,
    'ordering' => true,
    'order' => [0, 'desc'],
    'paging' => true,
    'lengthMenu' => [[10, 50, 100], [10, 50, 100]],
]));

echo Html::horizontalLine();

// Example 3: From Data

echo Html::h2('Example 3: Displaying data from a PHP array');

$users = [
    [
        'id' => 1,
        'username' => 'admin',
        'email' => 'example@example.com',
        'role' => 'admin',
        'enabled' => 1
    ],
    [
        'id' => 2,
        'username' => 'user',
        'email' => 'example2@example.com',
        'role' => 'user',
        'enabled' => 1
    ],
];

echo DataGrid::fromData('From PHP Array', $users, $theme, [
    'filters' => true,
    'ordering' => true,
    'order' => [0, 'asc'],
    'paging' => false,
    'lengthMenu' => [[10, 50, 100], [10, 50, 100]],
    'searching' => true,
    'info' => true,
    'export' => [
        'csv' => true,
        'tsv' => true
    ]
]);

echo Html::horizontalLine();

// Example 4 : Autoloading the DataGrid from Javascript, using the autoloader

echo Html::h2('Example 4: Autoloading the DataGrid from Javascript, using the autoloader');

echo Html::p('This example will autoload the DataGrid from Javascript, using the autoloader. The data is fetched from the database and then passed to the autoloader. Check source code to see how it is done.');

$db = new DB();

$pdo = $db->getConnection();

$usersData = $pdo->query('SELECT * FROM csp_approved_domains');

// PDO fetch now
$usersArray = $usersData->fetchAll(\PDO::FETCH_ASSOC);

$db->__destruct();

$autoloadArray = [
    [
        'type' => 'table',
        'parentDiv' => 'dataGridDataLoader',
        'tableOptions' => [
            'searching' => false,
            'ordering' => true,
            'order' => [0, 'desc'],
            //'paging' => true,
            //'lengthMenu' => [[25, 50, 100], [25, 50, 100]],
            'filters' => true,
            'info' => false,
            'export' => [
                'csv' => true,
                'tsv' => false
            ]
        ],
        'data' => $usersArray
    ],
    // Now another table but with fake random data 10000 rows
    [
        'type' => 'table',
        'parentDiv' => 'dataGridDataLoader',
        'tableOptions' => [
            'filters' => false,
        ],
        'data' => DataGrid::generateFakeData(501)
    ]
];

foreach ($autoloadArray as $array) {
    echo '<input type="hidden" name="autoload" value="' . htmlspecialchars(json_encode($array)) . '" />';
}

echo '<div id="dataGridDataLoader" class="max-w-full mx-2 my-12 flex flex-wrap flex-row justify-center items-center"></div>';

