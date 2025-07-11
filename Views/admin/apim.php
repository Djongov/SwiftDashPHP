<?php

declare(strict_types=1);

use Components\Forms;
use Components\Html;
use App\Security\Firewall;
use App\Api\Response;
use Components\DataGrid;

// Admin check
if (!$isAdmin) {
    Response::output('You are not an admin', 403);
}

Firewall::activate();

// Form to generate a new API key
$formOptions = [
    'inputs' => [
        'select' => [
            [
                'label' => 'Access',
                'name' => 'access',
                'id' => 'access',
                'title' => 'Access',
                'options' => [
                    [
                        'value' => 'read',
                        'text' => 'read'
                    ],
                    [
                        'value' => 'write',
                        'text' => 'write'
                    ]
                ],
                'selected' => 'read',
                'searchable' => false,
                'searchFlex' => 'flex-col',
                'description' => 'Select key permissions'
            ]
        ],
        'input' => [
            [
                'label' => 'Note',
                'name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'placeholder' => 'Enter a note for this key',
                'title' => 'Note for this key',
                'required' => true,
                'value' => '',
                'description' => 'This is a note for the API key, it can be anything you want.'
            ]
        ]
    ],
    // Now come the form options and the submit button options
    'theme' => $theme, // Optional, defaults to COLOR_SCHEME
    //'target' => '_blank', // Optional, defaults to _self
    'method' => 'POST', // Optional, defaults to POST
    'action' => '/api/admin/api-keys', // Required
    'additionalClasses' => 'qwerty power', // Optional
    //'reloadOnSubmit' => true,
    //'redirectOnSubmit' => '/dashboard',
    //'deleteCurrentRowOnSubmit' => false,
    //'confirm' => true,
    //'confirmText' => 'Are you sure you want to send this quack?', // Optional, defaults to "Are you sure?" if ommited
    'resultType' => 'html', // html or text, optional defaults to text
    //'doubleConfirm' => true,
    //'doubleConfirmKeyWord' => 'delete',
    //"stopwatch" => "example",
    'submitButton' => [
        'text' => 'Create API Key',
        'id' => uniqid(),
        'name' => 'submit',
        'type' => 'submit',
        'size' => 'medium',
        'disabled' => false,
    ]
];

echo Html::divBox(Forms::render($formOptions));

echo DataGrid::fromDBTable('api_keys', 'API Keys', $theme);

$requestIdFormOptions = [
    'inputs' => [
        'input' => [
            [
                'label' => 'Request ID',
                'name' => 'request_id',
                'id' => 'request_id',
                'type' => 'text',
                'placeholder' => 'Enter a request ID',
                'title' => 'Request ID',
            ]
        ]
    ],
    // Now come the form options and the submit button options
    'theme' => $theme, // Optional, defaults to COLOR_SCHEME
    //'target' => '_blank', // Optional, defaults to _self
    'method' => 'POST', // Optional, defaults to POST
    'action' => '/api/admin/request-id', // Required
    'additionalClasses' => 'qwerty power', // Optional
    //'reloadOnSubmit' => true,
    //'redirectOnSubmit' => '/dashboard',
    //'deleteCurrentRowOnSubmit' => false,
    //'confirm' => true,
    //'confirmText' => 'Are you sure you want to send this quack?', // Optional, defaults to "Are you sure?" if ommited
    'resultType' => 'html', // html or text, optional defaults to text
    //'doubleConfirm' => true,
    //'doubleConfirmKeyWord' => 'delete',
    //"stopwatch" => "example",
    'submitButton' => [
        'text' => 'Search Request ID',
        'id' => uniqid(),
        'name' => 'submit',
        'type' => 'submit',
        'size' => 'medium',
        'disabled' => false,
    ]
];

echo '<div class="m-4 max-w-full bg-gray-100 dark:bg-gray-800 p-4 rounded-lg shadow-md">';
    echo Forms::render($requestIdFormOptions);
echo '</div>';

echo DataGrid::fromDBTable('api_access_log', 'API Requests', $theme);
