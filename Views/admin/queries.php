<?php

declare(strict_types=1);

use Components\Forms;
use Components\Html;
use App\Security\Firewall;
use App\Api\Response;

// First firewall check
Firewall::activate();

// Admin check
if (!$isAdmin) {
    Response::output('You are not an admin', 403);
}

echo Html::h1('Queries', true);

echo Html::p('This is a custom query endpoint. You can use this to execute (almost) any query you want.');

$formOptions = [
    'inputs' => [
        'textarea' => [
            [
                'label' => 'Query',
                'name' => 'query',
                'required' => true,
                'value' => 'SELECT blocked_uri, violated_directive, domain, COUNT(*) as Count
FROM csp_reports
GROUP BY blocked_uri, violated_directive, domain
ORDER BY Count DESC;',
                'description' => 'Enter your query here',
                'cols' => 100,
                'rows' => 10,
            ]
        ]
    ],
    'action' => '/api/admin/queries',
    'resultType' => 'html',
    'stopwatch' => 'queries',
    'submitButton' => [
        'text' => 'Submit',
        'size' => 'small',
    ]
];

echo '<div class="p-4 m-4 max-w-full ' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700">';
    echo Forms::render($formOptions, $theme);
echo '</div>';
