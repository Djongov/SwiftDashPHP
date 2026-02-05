<?php

declare(strict_types=1);

use Components\Alerts;
use Components\Html;
use App\Logs\IISLogParser;
use App\Logs\AccessLogsParser;
use Components\DataGrid;
use App\Security\Firewall;
use App\Api\Response;

// First firewall check
Firewall::activate();

// Admin check
if (!$isAdmin) {
    Response::output('You are not an admin', 403);
}

// Prefer apache_getenv for mod_php
$accessLogDir = apache_getenv('ACCESS_LOGS') ?: getenv('ACCESS_LOGS');

if (!$accessLogDir) {
    echo Alerts::danger('No access logs directory set in environment (ACCESS_LOGS)');
    return;
}

$filePath = $accessLogDir;

// Check if the directory exists
if (!is_dir($filePath)) {
    echo Alerts::danger('The access logs directory (' . $filePath . ') does not exist');
    return;
}

if (!is_readable($filePath)) {
    echo Alerts::danger('The access logs directory (' . $filePath . ') is not readable');
    return;
}

if (!is_readable($filePath)) {
    echo Alerts::danger('The access log file (' . $filePath . ') is not readable');
    return;
}

// Get all the files from the directory
$files = scandir($filePath);

// Remove the . and .. from the array
$files = array_diff($files, ['.', '..']);

// Filter out unwanted files (e.g., "other_vhosts_access.log")
$files = array_filter(
    $files, function ($file) {
    // Ignore specific files like "other_vhosts_access.log"
    return $file !== 'other_vhosts_access.log';
    }
);

// Determine the operating system
$os = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'windows' : 'linux';

// Filter files based on OS and extension
if ($os === 'windows') {
    // On Windows, look for .log files
    $files = array_filter(
        $files, function ($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'log';
        }
    );
} else {
    // On Linux, look for apache2 logs or gzipped files
    $files = array_filter(
        $files, function ($file) {
        return preg_match('/custom_access\.log(\.gz)?$/', $file);
        }
    );
}

// If no log files found, display a message
if (empty($files)) {
    echo Alerts::danger('No access logs found');
    return;
}

// Check if each file is readable
foreach ($files as $file) {
    if (!is_readable($filePath . '/' . $file)) {
        echo Alerts::danger('The access log file ' . $file . ' is not readable');
        return;
    }
}

// Now the POST request to delete the file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the file is set
    if (!isset($_POST['file'])) {
        Response::output('No file set', 400);
    }
    // Check if the file exists
    if (!file_exists($filePath . '/' . $_POST['file'])) {
        Response::output('The access log file does not exist', 404);
    }
    // Check if the file is writable
    if (!is_writable($filePath . '/' . $_POST['file'])) {
        Response::output('The access log file is not writable', 403);
    }
    // Delete the file
    $file = basename($_POST['file']);
    $fullPath = $filePath . '/' . $file;

    $errorMessage = null;

    // Custom error handler to capture warning
    set_error_handler(
        function ($errno, $errstr) use (&$errorMessage) {
        $errorMessage = $errstr;
        return true; // prevent default warning output
        }
    );

    $success = unlink($fullPath);

    // Restore default error handler
    restore_error_handler();

    if ($success) {
        Response::output("The access log file $file was deleted", 200);
    } else {
        Response::output("The access log file $file could not be deleted: $errorMessage", 500);
    }
}

$isAccessLogsDirWritable = is_writable($filePath);

echo Html::h1('Access Logs', true);
echo Html::p($accessLogDir, ['text-center']);
echo Html::p('Log files in the directory:', ['text-center']);

// Sort by latest
arsort($files);

// Sort the files by filetime
$files = array_map(
    function ($file) use ($filePath) {
    return [
        'file' => $file,
        'time' => filemtime($filePath . '/' . $file),
        'size' => filesize($filePath . '/' . $file)
    ];
    }, $files
);

// Display the files
echo '<div class="flex md:flex-row flex-col flex-wrap items-center justify-center m-4">';
foreach ($files as $file) {
    $isCurrentFile = isset($_GET['file']) && $file['file'] === $_GET['file'];
    $borderColor = ($isCurrentFile) ? 'border-2 border-red-500' : 'border border-gray-900 dark:border-gray-400';
    echo '<div class="bg-gray-100 dark:bg-gray-900 max-w-lg mx-4 p-2 my-2 flex flex-col justify-center items-center ' . $borderColor . ' rounded-lg">';
        echo Html::a($file['file'], '?file=' . $file['file'] . '#log', $theme, '_self', ['ml-4']);
        echo Html::p(date('Y-m-d H:i:s', $file['time']), ['text-center']);
        // Calculate if it is bytes, KB or MB
        $delimiter = 1000000;
        // Now let's do a variable for the KB or MB
        $naming = 'MB';
    if ($file['size'] < 1000000) {
        $delimiter = 1000;
        $naming = 'KB';
    }

        echo Html::p('Size: ' . round($file['size'] / $delimiter, 2) . ' ' . $naming, ['text-center']);
        // If writable, show the delete button
        $deleteLogFormOptions = [
            "inputs" => [
                "hidden" => [
                    [
                        "name" => "file",
                        "value" => $file['file'],
                    ]
                ],
            ],
            'theme' => 'red',
            'action' => '?delete',
            'redirectOnSubmit' => '/adminx/access-logs',
            "submitButton" => [
                "text" => "Submit",
                'style' => '&#10060;',
                'title' => 'Delete the log file',
            ]
        ];

        echo ($isAccessLogsDirWritable) ? \Components\Forms::render($deleteLogFormOptions) : '';
        echo '</div>';
}
echo '</div>';

// Now if a files is chosen

if (!isset($_GET['file'])) {
    return;
}

$file = $_GET['file'];

// Check if the file exists
if (!file_exists($filePath . '/' . $file)) {
    echo Alerts::danger('The access log file does not exist');
    return;
}

// Check if the file is readable
if (!is_readable($filePath . '/' . $file)) {
    echo Alerts::danger('The access log file is not readable');
    return;
}

// Open the file
$handle = fopen($filePath . '/' . $file, 'r');

// Check if the file is opened
if (!$handle) {
    echo Alerts::danger('The access log file could not be opened');
    return;
}

// Read the file
echo '<h2 id="log" class="text-center">Log file: ' . htmlspecialchars($file) . '</h2>';

// If Windows, we parse the IIS log
if ($os === 'windows') {
    $parser = new IISLogParser($handle);
    $parsedLog = $parser->parse();
    // Now display some charts
    $chartsToShow = ['top5uris', 'top5status', 'methods', 'top5ips']; // These are array keys from ['counts']
    // Initiate the array
    $chartsArray = [];
    // Build the chart arrays
    foreach ($chartsToShow as $chartType) {
        $chartsArray[] = [
            'type' => 'piechart',
            'data' => [
                'parentDiv' => 'charts',
                'title' => $chartType,
                'width' => 250,
                'height' => 250,
                'labels' => array_keys($parsedLog['counts'][$chartType]),
                'data' => array_values($parsedLog['counts'][$chartType])
            ]
        ];
    }
    // In IIS there is a column Date and column Time, we can merge them
    $parsedLog['prasedData'] = array_map(
        function ($row) {
        $row['date'] = $row['date'] . ' ' . $row['time'];
        unset($row['time']);
        return $row;
        }, $parsedLog['prasedData']
    );
    echo '<div id="charts" class="flex flex-row flex-wrap p-6 justify-center">';
    // Create the hidden inputs so the JS can load the charts
    foreach ($chartsArray as $array) {
        echo '<input type="hidden" name="autoload" value="' . htmlspecialchars(json_encode($array)) . '" />';
    }
    echo '</div>';
    // Now display the data grid
    echo DataGrid::fromData($file, $parsedLog['prasedData'], $theme);
} else {
    $handle = gzopen($filePath . '/' . $file, 'r');

    $parser = new AccessLogsParser($handle);

    $parsedLog = $parser->parse();

    $log = $parsedLog['parsed_data'];

    $columns = $parsedLog['header_columns'];

    $counts = $parsedLog['counts'];

    $chartsArray = [];
    // Build the chart arrays
    foreach ($counts as $title => $chartType) {
        $chartsArray[] = [
            'type' => 'piechart',
            'data' => [
                'parentDiv' => 'charts',
                'title' => $title,
                'width' => 250,
                'height' => 250,
                'labels' => array_keys($chartType),
                'data' => array_values($chartType)
            ]
        ];
    }


    echo '<div id="charts" class="flex flex-row flex-wrap p-6 justify-center">';
    // Create the hidden inputs so the JS can load the charts
    foreach ($chartsArray as $array) {
        echo '<input type="hidden" name="autoload" value="' . htmlspecialchars(json_encode($array)) . '" />';
    }
    echo '</div>';

    //dd($chartsArray);

    echo DataGrid::fromData($file, $log, $theme);
}
