<?php

declare(strict_types=1);

namespace Components;

use App\Utilities\General;
use Components\Html;
use Components\Alerts;
use Components\DBButton;
use App\Security\CSRF;
use App\Database\DB;

class DataGrid
{
    public static array $tableOptions = [
        'searching' => true,
        'filters' => true,
        'ordering' => true,
        'order' => [[0, 'desc']],
        'paging' => true,
        'lengthMenu' => [[25, 50, 100, -1], [25, 50, 100, "All"]],
        'info' => true,
        'export' => [
            'csv' => true,
            'tsv' => true
        ]
    ];
    private static function getDeleteLoader($id, $theme): string
    {
        return '
        <div id="' . $id . '-delete-loading-screen" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-72 h-28 bg-slate-50 dark:bg-slate-600 hidden border border-black dark:border-slate-200">
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 flex flex-row">
            <p id="' . $id . '-delete-loading-screen-text" class="text-' . $theme . '-500">Deleting...</p>
                <div role="status">
                    <svg aria-hidden="true" class="inline mx-2 w-8 h-8 text-gray-200 dark:text-white animate-spin fill-' . $theme . '-500 dark:fill-' . $theme . '-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                    </svg>
                </div>
            </div>
        </div>';
    }
    private static function constructThead($delete, $edit, $theme, $sorting, $totalColumns): string
    {
        $html = '';
        $html .= '<thead class="bg-gray-200 dark:bg-gray-700 sticky top-0 border-collapse">';
            $thClass = "px-4 py-2 text-center text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer";
            $html .= '<tr>';
                // If delete is true, have a All th
        if ($delete) {
            $html .= '<th scope="col" class="' . $thClass . '"><input type="checkbox" class="select-all" title="select all visible rows" /><br />All</th>';
        }
        foreach ($totalColumns as $cols) {
            if ($sorting) {
                $html .= '<th scope="col" class="' . $thClass . '"><span>' . $cols . '</span> <span class="text-xs text-' . $theme . '-500">&#x25B2;&#x25BC;</span></th>';
            } else {
                $html .= '<th scope="col" class="' . $thClass . '">' . $cols . '</th>';
            }
        }
                // and one more th for the actions if enabled
        if ($delete || $edit) {
            $html .= '<th scope="col" class="' . $thClass . '">Actions</th>';
        }
            $html .= '</tr>';
            // 2nd row for filters will be created by JS
        $html .= '</thead>';
        return $html;
    }
    /*
    * @param ?string optional $title. The title will be a h2 tag above the table
    * @param array $data The data to be displayed in the table
    * @param string $theme The color theme of the table, usually enough to pass $theme
    * @param array $tableOptions The options for the table. They are the following:
    * - searching: boolean, default true
    * - filters: boolean, default true
    * - ordering: boolean, default true
    * - order: array, default [[0, 'desc']]
    * - paging: boolean, default true
    * - lengthMenu: array, default [[25, 50, 100, -1], [25, 50, 100, "All"]]
    * @return string
    */
    public static function fromData(?string $title, array $data, string $theme, ?array $tableOptions = null): string
    {
        return self::createTable('', $title, $data, $theme, false, false, $tableOptions);
    }
    public static function calculateTableOptionsLengthMenu(array $data): array
    {
        $maxInputVars = ini_get('max_input_vars');
        $totalRows = count($data);
        // Starting point is based on total rows
        if ($totalRows < 25) {
            return [[25, -1], [25, 'All']];
        } else {
            $lengthMenu = [[25], [25]];
        }
        if ($totalRows >= 50) {
            array_push($lengthMenu[0], 50);
            array_push($lengthMenu[1], 50);
        }
        if ($totalRows >= 100) {
            array_push($lengthMenu[0], 100);
            array_push($lengthMenu[1], 100);
        }
        if ($totalRows >= 250) {
            array_push($lengthMenu[0], 250);
            array_push($lengthMenu[1], 250);
        }
        if ($totalRows >= 500) {
            array_push($lengthMenu[0], 500);
            array_push($lengthMenu[1], 500);
        }
        // Add a 1000 if we have more than 1000 rows and max_input_vars is more than 1010
        if ($totalRows >= 1000 && $maxInputVars >= 1010) {
            array_push($lengthMenu[0], 1000);
            array_push($lengthMenu[1], 1000);
        }
        // If we have more rows than max_input_vars, then we need to add the total number of max_input_vars - 10, -10 because we have some datagrid specific vars being passed as well
        if ($totalRows >= $maxInputVars) {
            array_push($lengthMenu[0], $maxInputVars - 10);
            array_push($lengthMenu[1], $maxInputVars - 10);
        }

        // In the end add the -1 and all
        array_push($lengthMenu[0], -1);
        array_push($lengthMenu[1], 'All');

        return $lengthMenu;
    }
    public static function simpleTable(array $data, string $theme): string
    {
        return self::createTable('', null, $data, $theme, false, false, [
            'searching' => false,
            'filters' => false,
            'ordering' => false,
            'order' => [[0, 'desc']],
            'paging' => false,
            'lengthMenu' => self::calculateTableOptionsLengthMenu($data),
            'info' => false,
            'export' => [
                'csv' => true,
                'tsv' => false
            ]
        ]);
    }
    public static function fromDBTable(string $dbTable, ?string $title, string $theme, bool $edit = true, bool $delete = true, $orderBy = 'id', $sortBy = 'desc', ?array $tableOptions = null): string
    {
        // We pull from table
        if ($sortBy !== 'asc' && $sortBy !== 'desc') {
            return Alerts::danger('Invalid sort order. Please use either "asc" or "desc"');
        }
        $db = new DB();
        $pdo = $db->getConnection();
        try {
            $stmt = $pdo->query('SELECT * FROM ' . $dbTable . ' ORDER BY ' . $orderBy . ' ' . strtoupper($sortBy) . '');
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return Alerts::danger('Error fetching data from the database: ' . $e->getMessage());
        }

        if (!$data) {
            return Alerts::danger('No results for table "' . $dbTable . '"');
        }

        return self::createTable($dbTable, $title, $data, $theme, $edit, $delete, $tableOptions);
    }
    public static function fromQuery(string $dbTable, string $query, ?string $title, string $theme, bool $edit = true, bool $delete = true, ?array $tableOptions = null): string
    {
        // First of all, check if query has SELECT in it and if it does, we need to make sure that id has been passed
        if (($edit || $delete) && (stripos($query, 'SELECT') !== false && stripos($query, 'id') === false)) {
            return Alerts::danger('Please include id column in your query, if you have enabled edit or delete options.');
        }
        // We pull from query
        $db = new DB();
        $pdo = $db->getConnection();
        try {
            $stmt = $pdo->query($query);
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return Alerts::danger('Error fetching data from the database: ' . $e->getMessage());
        }

        if (!$data) {
            return Alerts::danger('No results for query "' . $query . '"');
        }

        return self::createTable($dbTable, $title, $data, $theme, $edit, $delete, $tableOptions);
    }
    private static function createTable(string $dbTable, ?string $title, array $data, string $theme, bool $edit = true, bool $delete = true, ?array $tableOptions = null): string
    {
        $tableOptions = $tableOptions ?? self::$tableOptions;

        $correctTableOptions = ['searching', 'filters', 'ordering', 'order', 'paging', 'lengthMenu', 'info', 'export'];

        // We don't want to be restricting that the all of the keys are present, but we want to make sure that the keys are correct
        foreach ($tableOptions as $key => $value) {
            if (!in_array($key, $correctTableOptions)) {
                return Alerts::danger('Invalid table option: ' . $key);
            }
        }

        // Let's do the info, if info is not set, let's only turn it on if searching is on or paging is on
        if (!isset($tableOptions['info'])) {
            if (isset($tableOptions['searching']) && $tableOptions['searching'] || isset($tableOptions['paging']) && $tableOptions['paging']) {
                $tableOptions['info'] = true;
            } else {
                $tableOptions['info'] = false;
            }
        }

        // Let's calculate the lengthMenu by the total number of rows
        $tableOptions['lengthMenu'] = self::calculateTableOptionsLengthMenu($data);

        $html = '';
        foreach ($data as $a => $b) {
            if (!is_array($b)) {
                $data = General::assocToIndexed($data);
            }
            break;
        }
        $totalCount = count($data);
        if ($totalCount < 1) {
            $noResultsText = ($title) ? 'No results for "' . $title . '"' : 'No results found';
            $html .= Alerts::danger($noResultsText);
            return $html;
        }

        if (!isset($tableOptions['lengthMenu'])) {
            // Let's calculate the lengthMenu by the total number of rows
            $defaultLengthMenu = [[25, 50, 100, -1], [25, 50, 100, "All"]];
            if ($totalCount >= 1000) {
                // Push 1000 to the first array
                array_unshift($defaultLengthMenu[0], 500);
                array_unshift($defaultLengthMenu[0], 1000);
                // Push 1000 to the 2nd array but minus last element
                array_unshift($defaultLengthMenu[1], 500);
                array_unshift($defaultLengthMenu[1], 1000);
            }
            // If more than 10000
            if ($totalCount >= 10000) {
                // Push 10000 to the first array
                array_unshift($defaultLengthMenu[0], 5000);
                array_unshift($defaultLengthMenu[0], 10000);
                // Push 10000 to the 2nd array but minus last element
                array_unshift($defaultLengthMenu[1], 5000);
                array_unshift($defaultLengthMenu[1], 10000);
            }
            $tableOptions['lengthMenu'] = $defaultLengthMenu;
        }

        if (count($data) >= 5) {
            $tableOptions['paging'] = true;
        } else {
            $tableOptions['paging'] = false;
        }

        $originalDBTable = $dbTable;
        if ($dbTable === '' || $dbTable === null) {
            $dbTable = 'table-' . uniqid();
        }
        $id = $dbTable . '-' . uniqid();
        $id = str_replace(' ', '', $id);
        $id = strtolower($id);
        if ($delete) {
            // The loading screen shown when deleting items
            $html .= self::getDeleteLoader($id, $theme);
        }
        $filters = $tableOptions['filters'];
        // All encompassing div
        $html .= '<div class="my-4">';
        $html .= '<div class="ml-2 mt-4 ' . DATAGRID_TEXT_COLOR_SCHEME . ' ' . DATAGRID_TEXT_DARK_COLOR_SCHEME . '">';
        $html .= ($title) ? Html::h2($title, true) : null;
        $html .= ($tableOptions['info'] === true) ? '<p class="text-sm">Results: <span id="' . $id . '-total">' . $totalCount . '</span></p>' : null;
        if ($delete || $edit) {
            $html .= '<p class="text-sm">Filtered: <span id="' . $id . '-filtered">' . $totalCount . '</span></p>';
            $html .= '<p class="text-sm">Selected: <span id="' . $id . '-selected">0</span></p>';
        }
        $html .= '</div>';
        // Table
        $html .= '<div id="' . $id . '-container" class="m-4 overflow-x-auto">';
        $html .= ($delete) ? '<form class="delete-selected-form">' : null;
        // The table loading div
        $html .= '<div id="' . $id . '-loading-table" class="mt-12 bg-' . $theme . '-500 h-full w-full text-center text-white">Data Loading... Please wait<svg class="inline mx-4 w-8 h-8 text-gray-200 dark:text-white animate-spin fill-blue-600 dark:fill-' . $theme . '-500" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg></div>';
        // Table itself
        $html .= '<table id="' . $id . '" class="hidden datagrid mt-4 min-w-full table-auto text-center">';
            // Construct Head
            $totalColumns = self::getColumns($data);
            // If the totalColumns is just 0 and 1, then we don't need to show thead at all
        if (count($totalColumns) === 2 && $totalColumns[0] === 0 && $totalColumns[1] === 1) {
            $totalColumns = [
                'column1',
                'column2'
            ];
        }
            $html .= self::constructThead($delete, $edit, $theme, $tableOptions['ordering'], $totalColumns);
            $html .= '<tbody>';
                $csrfToken = CSRF::create();
                $counter = 0;
                // Time to loop through the data array and build the tbody
        foreach ($data as $indexes => $arrays) {
            $counter++;
            $currentId = $arrays['id'] ?? $counter;
            $html .= '<tr tabindex="' . $indexes . '" data-row-id="' . $currentId . '" class="even:bg-gray-200 odd:bg-gray-100 dark:even:bg-gray-700 dark:odd:bg-gray-600 focus:bg-' . $theme . '-500 dark:focus:bg-gray-500">';
                $tdClassArray = ['px-4', 'py-2', 'text-sm', 'text-gray-900', 'dark:text-gray-300', 'max-w-xs', 'break-words', 'focus-within:text-white', 'focus-within:text-semibold'];
            foreach ($arrays as $column => $value) {
                if ($column === 'id' && $delete) {
                    $html .= '<td class="' . implode(' ', $tdClassArray) . '"><input type="checkbox" value="' . $currentId . '" name="row[]"></td>';
                }
                    // Convert nulls or empty strings to (Empty) so it's easier to filter
                if ($value === null || $value === '') {
                    $value = '(Empty)';
                }
                if ($column === 'id') {
                    $html .= '<td class="' . implode(' ', $tdClassArray) . '" data-row-id="' . $value . '">' . $value . '</td>';
                } else {
                    $tdTitle = '';
                    if (is_string($value)) {
                        if (strlen($value) > 100) {
                            $tdClassArray[] = 'break-words';
                            // Also add the full untruncated value as a title
                            $tdTitle = ' title="' . $value . '"';
                        }
                        $value = htmlspecialchars($value);
                    }
                    if (is_array($value)) {
                        $value = json_encode($value, JSON_PRETTY_PRINT);
                        if (strlen($value) > 100) {
                            $tdClassArray[] = 'break-words';
                            // Also add the full untruncated value as a title
                            $tdTitle = ' title="' . $value . '"';
                        }
                    }
                    $html .= '<td class="' . implode(' ', $tdClassArray) . '"' . $tdTitle . '>' . $value . '</td>';
                }
            }
            if ($delete || $edit) {
                        // Whether we have delet or edit, we will do another <td>
                        $html .= '<td class="' . implode(' ', $tdClassArray) . '">
                                <div class="flex items-center justify-center">';
                if ($edit) {
                    //$html .= '<button data-table="' . $originalDBTable . '" data-id="' . $currentId . '" data-columns="' . implode(',', $totalColumns) . '" data-csrf="' . $csrfToken . '" type="button" class="edit ml-2 my-2 block border dark:border-gray-400 text-white dark:text-gray-100 bg-gray-500 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-400">Edit</button>';
                    $html .= DBButton::editButton($originalDBTable, $totalColumns, $currentId, 'Edit id ' . $currentId);
                }
                if ($delete) {
                    //$html .= '<button data-table="' . $originalDBTable . '" data-id="' . $currentId . '" type="button" data-csrf="' . $csrfToken . '" class="delete ml-2 my-2 block text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">Delete</button>';
                    $html .= DBButton::deleteButton($originalDBTable, $currentId, 'Delete this entry', 'Are you sure you want to delete entry with id <b>' . $currentId . '</b> from table <b>' . $originalDBTable . '</b>?');
                }
                            $html .= '</div>
                            </td>';
            }
                            $html .= '</tr>';
        }
            $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        // Delete all modal starts here
        if ($delete) {
            // Delete selected button
            $html .= '<button id="' . $id . '-mass-delete-modal-trigger" class="delete-selected block ml-2 my-2 text-white bg-red-500 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800" type="button" data-modal-target="' . $id . '-mass-delete-modal" data-modal-toggle="' . $id . '-mass-delete-modal">
                Delete selected
            </button>';
            $html .= '
            <div id="' . $id . '-mass-delete-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full justify-center items-center" aria-hidden="true">
                <div class="relative p-4 w-full max-w-md h-full md:h-auto">
                    <div class="relative ' . LIGHT_COLOR_SCHEME_CLASS . ' border border-gray-400 dark:border-gray-300 rounded-lg shadow dark:bg-gray-700">
                        <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="' . $id . '-mass-delete-modal">
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                        <div class="p-6 text-center">
                            <svg aria-hidden="true" class="mx-auto mb-4 w-14 h-14 text-gray-400 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h3 id="' . $id . '-mass-delete-modal-text" class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this entry?</h3>
                            <button data-modal-toggle="' . $id . '-mass-delete-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                Yes, I\'m sure
                            </button>
                            <button data-modal-toggle="' . $id . '-mass-delete-modal" type="button" class="text-gray-500 ' . LIGHT_COLOR_SCHEME_CLASS . ' hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancel</button>
                        </div>
                    </div>
                </div>
            </div>
            ';
            // Show which table we are deleting from
            $html .= '<input type="hidden" name="deleteRecords" value="' . $dbTable . '" />';
            // Include the CSRF token
            $html .= '<input type="hidden" name="csrf_token" value="' . $csrfToken . '" />';
            // Close the mass delete form
            $html .= '</form>';
        }
        // Export functionality
        if (isset($tableOptions['export'])) {
            $exportButtonsArray = [];
            $allowedExports = ['csv', 'tsv'];
            foreach ($tableOptions['export'] as $exportType => $exportValue) {
                if (!in_array($exportType, $allowedExports)) {
                    throw new \Exception('Invalid export type: ' . $exportType);
                }
                // Check if boolean too
                if (!is_bool($exportValue)) {
                    throw new \Exception('Export value must be a boolean');
                }
                if ($exportValue) {
                    // Now let's define the export buttons
                    $exportButtonsArray['Export in ' . strtoupper($exportType)] = '/api/tools/export-' . $exportType;
                }
            }
            $html .= '<div class="flex">';
            foreach ($exportButtonsArray as $name => $link) {
                $html .= '<form action="' . $link . '" method="post" target="_blank">';
                    $html .= '<button type="submit" class="p-2 ml-2 mt-2 text-gray-500 ' . LIGHT_COLOR_SCHEME_CLASS . ' hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600" title="' . $name . '">';
                        $html .= $name;
                    $html .= '</button>';
                    $html .= '<input type="hidden" name="data" value="' . htmlentities(json_encode($data)) . '" />';
                    $html .= '<input type="hidden" name="type" value="' . $id . '" />';
                    $html .= '<input type="hidden" name="csrf_token" value="' . $csrfToken . '" />';
                $html .= '</form>';
            }
            $html .= '</div>';
        }
        $html .= PHP_EOL;
        // Here we will build the skip columns array to instruct JS to skip making filters for certain columns. What we want is to skip the first column only when $delete is true, and always skip the last column + 1, the Actions column
        if ($delete && !$edit) {
            $skipColumnArray = '[0, ' . (count($totalColumns) + 1) . ']';
        } elseif (!$delete && $edit) {
            // Skip first and last columns
            $skipColumnArray = '[0, ' . count($totalColumns) . ']';
        } elseif ($delete && $edit) {
            $skipColumnArray = '[0, ' . (count($totalColumns) + 1) . ']';
        } else {
            $skipColumnArray = '[]';
        }
        if ($filters) {
            $filterJS = <<<JS
            buildDataGridFilters(table, '$id', $skipColumnArray);
                // On every re-draw, rebuild them
                table.on('draw', () => {
                    buildDataGridFilters(table, '$id', $skipColumnArray);
                });
            JS;
        } else {
            $filterJS = '';
        }
        unset($tableOptions['filters']);
        $jsonEncodedTableOptions = json_encode($tableOptions);
        $html .= <<<JS
            <script nonce="1nL1n3JsRuN1192kwoko2k323WKE">
                $(document).ready(() => {
                    const table = drawDataGrid('$id', $jsonEncodedTableOptions);
                    $filterJS
                });
            </script>
        JS;

        // Close all encompassing div
        $html .= '</div>';
        return $html;
    }
    public static function getColumns($data): array
    {
        // Let's calculate the columns
        $totalColumns = [];
        foreach ($data as $arrays) {
            foreach ($arrays as $column => $data) {
                array_push($totalColumns, $column);
            }
        }
        // Now lets make the totalColumns unique
        return array_unique($totalColumns);
    }
    // generate fake data
    public static function generateFakeData(int $rows): array
    {
        $data = [];
        for ($i = 0; $i < $rows; $i++) {
            $data[] = [
                'id' => $i + 1,
                'name' => General::randomString(10),
                'email' => General::randomString(10) . '@example.com',
                'phone' => General::randomString(10),
                'address' => General::randomString(10),
                'city' => General::randomString(10),
                'state' => General::randomString(10),
                'zip' => General::randomString(10),
                'country' => General::randomString(10),
                'created_at' => General::randomString(10),
                'updated_at' => General::randomString(10),
            ];
        }
        return $data;
    }
}
