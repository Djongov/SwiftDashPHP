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
$fileName = $type . "-data-" . date('Y-m-d-H-i-s') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $fileName . '"');

$output = fopen('php://output', 'w');

// Assuming that the first element of $data contains the keys (column names)

$columnNames = array_keys(reset($data));
fputcsv($output, $columnNames, ";", '"', "\\");


foreach ($data as $row) {
    fputcsv($output, array_map($neutraliseCell, (array) $row), ";", '"', "\\");
}


fclose($output);
exit;
