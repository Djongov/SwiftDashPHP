<?php

declare(strict_types=1);

use Models\APIKeys;
use Components\Alerts;
use Components\Forms;
use Components\Html;
use Components\DataGrid;

$apiKey = new APIKeys();

$apiKeys = $apiKey->getApiKeyByNote($usernameArray['username']);

if (!$apiKeys) {
    $formOptions = [
        'inputs' => [],
        // Now come the form options and the submit button options
        'theme' => $theme, // Optional, defaults to COLOR_SCHEME
        'method' => 'POST', // Optional, defaults to POST
        'action' => '/api/api-keys', // Required
        'reloadOnSubmit' => true,
        'resultType' => 'html',
        'submitButton' => [
            'text' => 'Create API Key',
            'id' => uniqid(),
            'size' => 'medium',
            'disabled' => false,
        ]
    ];

    echo Html::divBox(
        '<div class="text-center mb-4">'
            . Html::h3('No API Keys Found') .
            Html::p('API keys allow you to access our services programmatically. Create one to get started!')
            . Forms::render($formOptions) .
        '</div>',
    );

    return;
}

echo Html::h2('Your API Keys');

$deleteApiKeyFormOptions = [
    'inputs' => [],
    'theme' => 'red',
    'method' => 'DELETE',
    'action' => '/api/api-keys?csrf_token=' . $_SESSION['csrf_token'],
    'confirm' => true,
    //'reloadOnSubmit' => true,
    'resultType' => 'html',
    'submitButton' => [
        'text' => 'Delete API Key',
        'id' => uniqid(),
        'size' => 'small',
    ]
];

echo Html::divBox(
    Html::p('Here is your API key. You can use it to access our services programmatically.') . Html::p($apiKeys, ['c0py']) . Forms::render($deleteApiKeyFormOptions)
);

$apiKeyData = $apiKey->get($apiKeys);

$accessLogsData = $apiKey->getAccessLogsPerApiKey($apiKeys);

$groupedRequests = [];

foreach ($accessLogsData as $log) {
    $date = substr($log['date_created'], 0, 10); // Extract "YYYY-MM-DD"
    if (!isset($groupedRequests[$date])) {
        $groupedRequests[$date] = 0;
    }
    $groupedRequests[$date]++;
}

$lineChartData = [
    'labels' => array_keys($groupedRequests),
    'datasets' => [
        [
            'label' => 'Requests over time',
            'data' => array_values($groupedRequests)
        ]
    ]
];


$now = new DateTime('now', new DateTimeZone('UTC'));
$reset = new DateTime('tomorrow 00:00', new DateTimeZone('UTC'));

$nextReset = $reset->getTimestamp() - $now->getTimestamp();

$days = floor($nextReset / 86400);
$hours = floor(($nextReset % 86400) / 3600);
$minutes = floor(($nextReset % 3600) / 60);
$seconds = $nextReset % 60;

echo Html::p('Your daily quota resets in: ' . sprintf('%d days, %d hours, %d minutes, and %d seconds', $days, $hours, $minutes, $seconds));

echo '<div id="doughnut-limits-holder" class="my-12 flex flex-wrap justify-start items-start flex-row-reverse">';
    $chartsArray = [
        [
            'type' => 'linechart',
            'data' => [
                'parentDiv' => 'doughnut-limits-holder',
                'title' => 'Line Chart',
                'width' => 400,
                'height' => 200,
                'labels' => array_keys($groupedRequests),
                'datasets' => [
                    [
                        'label' => 'Requests over time',
                        'data' => array_values($groupedRequests)
                    ]
                ]
            ]
        ],
        [
            'type' => 'gaugechart',
            'data' => [
                'parentDiv' => 'doughnut-limits-holder',
                'title' => 'API Key Usage Limit',
                'width' => 250,
                'height' => 250,
                'data' => [$apiKeyData['executions'], $apiKeyData['executions_limit']]
            ]
        ]
    ];
    foreach ($chartsArray as $array) {
        echo '<input type="hidden" name="autoload" value="' . htmlspecialchars(json_encode($array)) . '" />';
    }
    echo '<div class="w-full max-w-4xl mx-auto px-4 py-8 text-gray-800 dark:text-gray-200">';
        // Let's strip some data from the access log
        foreach ($accessLogsData as &$row) {
            unset($row['id'], $row['api_key'], $row['last_updated'], $row['last_updated_by']);
        }
        echo DataGrid::fromData('API Access Logs', $accessLogsData, $theme);
    echo '</div>';
echo '</div>';

// Show how much time until the next API key reset (which is at 00:00 UTC)

//echo Html::h2('Training Ground');

