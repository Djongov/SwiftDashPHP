<?php

declare(strict_types=1);

use App\Api\Checks;
use App\Api\Response;

$checks = new Checks($loginInfo, $_POST);

$checks->apiChecksNoCSRFHeader(false);

$data = isset($_POST['data']) ? json_decode((string) $_POST['data'], true) : null;

if (!is_array($data) || count($data) === 0 || !is_array(reset($data))) {
    Response::output('No data to export', 400);
}

// Cells starting with = + - @ or a tab/CR are interpreted as formulas by spreadsheets, neutralise them
$neutraliseCell = function ($cell) {
    if (is_array($cell) || is_object($cell)) {
        $cell = json_encode($cell);
    }
    $cell = (string) $cell;
    return ($cell !== '' && strpbrk($cell[0], "=+-@\t\r") !== false) ? "'" . $cell : $cell;
};

if (isset($_POST['type'])) {
    $type = htmlspecialchars($_POST['type']);
} else {
    $type = '';
}

// file name for download
$fileName = $type . "-logs-data-" . date('Y-m-d-H-i-s') . ".tsv";

$excelData = '';
$uniqueHeads = [];


$uniqueHeads = array_keys(reset($data));


// Display column names as first row
$excelData = implode("\t", $uniqueHeads) . "\n";

// And the actual data under the columns
foreach ($data as $value) {
    $row = [];
    foreach ($uniqueHeads as $key) {
        // Tabs and newlines inside a value would break the row layout
        $row[] = str_replace(["\t", "\r", "\n"], ' ', $neutraliseCell($value[$key] ?? ''));
    }
    $excelData .= implode("\t", $row) . "\n";
}

// Headers for download
header("Content-Type: text/tab-separated-values");
header("Content-Disposition: attachment; filename=\"$fileName\"");

// Render excel data
echo $excelData;
exit;
