<?php declare(strict_types=1);

use Components\Alerts;
use Components\Html;
use App\Logs\IISLogParser;
use Components\DataGrid;
use App\Security\Firewall;
use App\Api\Response;


// First firewall check
Firewall::activate();

// Admin check
if (!$isAdmin) {
    Response::output('You are not an admin', 403);
}

// Get the dir
if (!isset($_ENV['ACCESS_LOGS'])) {
    echo Alerts::danger('No access logs directory set in .env file');
    return;
}

$dir = $_ENV['ACCESS_LOGS'];

// Check if the dir exists
if (!is_dir($dir)) {
    echo Alerts::danger('The access logs directory does not exist');
    return;
}

// Check if readable
if (!is_readable($dir)) {
    echo Alerts::danger('The access logs directory is not readable');
    return;
}
// Get all the files from the dir
$files = scandir($dir);

// Remove the . and .. from the array
$files = array_diff($files, ['.', '..']);

// Find out if we run Windows or Linux
$os = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'windows' : 'linux';

// If we run Windows, we look for .log files
if ($os === 'windows') {
    $files = array_filter($files, function ($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'log';
    });
} else {
    // If we run Linux, we look for .log files and .log.gz files
    $files = array_filter($files, function ($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'log' || pathinfo($file, PATHINFO_EXTENSION) === 'gz';
    });
}

if (!$files || count($files) === 0) {
    echo Alerts::danger('No access logs found');
    return;
}

// Check if each files is readable
foreach ($files as $file) {
    if (!is_readable($dir . '/' . $file)) {
        echo Alerts::danger('The access log file ' . $file . ' is not readable');
        return;
    }
}

echo Html::h1('Access Logs', true);

echo Html::p($_ENV['ACCESS_LOGS'], ['text-center']);

echo Html::p('Log files in the directory:', ['text-center']);

// Sort by latest
arsort($files);

// Sort the files by filetime
$files = array_map(function ($file) use ($dir) {
    return [
        'file' => $file,
        'time' => filemtime($dir . '/' . $file),
        'size' => filesize($dir . '/' . $file)
    ];
}, $files);

// Display the files
echo '<div class="flex flex-row flex-wrap my-4">';
    foreach ($files as $file) {
        echo '<div class="max-w-lg mx-auto p-2 my-2 flex flex-col justify-center items-center border border-gray-900 dark:border-gray-400 rounded-lg">';
            echo Html::a($file['file'], '?file=' . $file['file'], $theme, '_self', ['ml-4']);
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
        echo '</div>';
    }
echo '</div>';

// Now if a files is chosen

if (!isset($_GET['file'])) {
    return;
}

$file = $_GET['file'];

// Check if the file exists
if (!file_exists($dir . '/' . $file)) {
    echo Alerts::danger('The access log file does not exist');
    return;
}

// Check if the file is readable
if (!is_readable($dir . '/' . $file)) {
    echo Alerts::danger('The access log file is not readable');
    return;
}

// Open the file
$handle = fopen($dir . '/' . $file, 'r');

// Check if the file is opened
if (!$handle) {
    echo Alerts::danger('The access log file could not be opened');
    return;
}

// Read the file
//$contents = fread($handle, filesize($dir . '/' . $file));

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
    $parsedLog['prasedData'] = array_map(function ($row) {
        $row['date'] = $row['date'] . ' ' . $row['time'];
        unset($row['time']);
        return $row;
    }, $parsedLog['prasedData']);
    echo '<div id="charts" class="flex flex-row flex-wrap p-6 justify-center">';
    // Create the hidden inputs so the JS can load the charts
    foreach ($chartsArray as $array) {
        echo '<input type="hidden" name="autoload" value="' . htmlspecialchars(json_encode($array)) . '" />';
    }
    echo '</div>';
    // Now display the data grid
    echo DataGrid::fromData($file, $parsedLog['prasedData'], $theme);
}

