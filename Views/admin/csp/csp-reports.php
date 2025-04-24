<?php

declare(strict_types=1);

use App\Security\Firewall;
use App\Api\Response;
use App\Database\DB;
use Components\Alerts;
use Components\DataGrid;

// First firewall check
Firewall::activate();

// Admin check
if (!$isAdmin) {
    Response::output('You are not an admin', 403);
}

$db = new DB();

$pdo = $db->getConnection();

$stmt = $pdo->prepare('SELECT id,domain,url,referrer,violated_directive,effective_directive,disposition,blocked_uri,line_number,column_number,source_file,status_code,script_sample FROM csp_reports ORDER BY id DESC');

$stmt->execute();

$cspArray = $stmt->fetchAll(\PDO::FETCH_ASSOC);

if (!$cspArray) {
    echo Alerts::danger('No CSP reports data found');
    return;
}

// Let's build some autoload charts

function filterValidValues($array)
{
    return array_filter(
        $array, function ($value) {
        return $value !== null && (is_string($value) || is_int($value));
        }
    );
}

// We will need an array with the values of domain
$domains = array_column($cspArray, 'domain');
// Now we need to find out how many entries we have with this domain
$domainsCount = array_count_values(filterValidValues($domains));

// sort the array by value
arsort($domainsCount);

// Now the domain data for the chart
$domainData = [
    'type' => 'piechart',
    'data' => [
        'parentDiv' => 'csp-charts',
        'title' => 'Domains',
        'width' => 300,
        'height' => 300,
        'labels' => array_keys($domainsCount),
        'data' => array_values($domainsCount)
    ]
];

$violatedDirectives = array_column($cspArray, 'violated_directive');
$violatedDirectivesCount = array_count_values(filterValidValues($violatedDirectives));

// sort the array by value
arsort($violatedDirectivesCount);

$violatedDirectivesData = [
    'type' => 'piechart',
    'data' => [
        'parentDiv' => 'csp-charts',
        'title' => 'Violated Directives',
        'width' => 300,
        'height' => 300,
        'labels' => array_keys($violatedDirectivesCount),
        'data' => array_values($violatedDirectivesCount)
    ]
];

$statusCodes = array_column($cspArray, 'status_code');
$statusCodesCount = array_count_values(filterValidValues($statusCodes));

// sort the array by value
arsort($statusCodesCount);

$statusCodesData = [
    'type' => 'piechart',
    'data' => [
        'parentDiv' => 'csp-charts',
        'title' => 'Status Codes',
        'width' => 300,
        'height' => 300,
        'labels' => array_keys($statusCodesCount),
        'data' => array_values($statusCodesCount)
    ]
];

echo '<div id="csp-charts" class="flex flex-row flex-wrap p-6 justify-center">';
    $chartsArray = [
        $domainData,
        $violatedDirectivesData,
        $statusCodesData
    ];
    // Now go through them and create an input hidden for each
    foreach ($chartsArray as $array) {
        echo '<input type="hidden" name="autoload" value="' . htmlspecialchars(json_encode($array)) . '" />';
    }
    echo '</div>';

// Let's do a blocked_uri table
    $blockedUri = array_column($cspArray, 'blocked_uri');

// Let's calculate the total number of blocked_uri based on the occurrence of each key in the $blockedUri array
    $indexedArray = array_count_values($blockedUri);

    echo '<div class="mx-4 max-w-full overflow-auto flex justify-center">';
    echo DataGrid::fromData(
        'blocked_uri', $indexedArray, $theme, [
        //'sorting' => true,
        'filters' => true,
        'ordering' => true,
        'order' => [1, 'desc'],
        'paging' => true,
        'lengthMenu' => [[10, 50, 100, -1], [10, 50, 100, 'All']],
        ]
    );
    echo '</div>';

    $cspReportsQuery = 'SELECT id,date_created,domain,url,referrer,violated_directive,effective_directive,disposition,blocked_uri,line_number,column_number,source_file,script_sample FROM csp_reports';

    echo DataGrid::fromQuery(
        'csp_reports', $cspReportsQuery, 'CSP Reports', $theme, true, true, [
        'filters' => true,
        'ordering' => true,
        'order' => [0, 'asc'],
        'paging' => true,
        'lengthMenu' => [[10, 50, 100, -1], [10, 50, 100, 'All']],
        'searching' => true,
        'info' => true,
        'export' => [
        'csv' => true
        ]
        ]
    );

    $db->__destruct();
