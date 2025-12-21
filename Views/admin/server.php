<?php

declare(strict_types=1);

use Components\Forms;
use Components\Html;
use App\Logs\SystemLog;
use App\Security\Firewall;
use App\Api\Response;
use Components\DataGrid;
use Components\Alerts;

// First firewall check
Firewall::activate();

// Admin check
if (!$isAdmin) {
    Response::output('You are not an admin', 403);
}

if (!$isAdmin) {
    SystemLog::write('Got unauthorized for admin page', 'Access');
    Response::output('You are not authorized to view this page', 401);
}

// First check if error_log is there
$errorLog = ini_get('error_log');

if (empty($errorLog)) {
    echo Html::h2('Error Log', true);
    echo Alerts::danger('No error log file is set in php.ini');
} else {
    echo Html::h2('PHP Errors');
    $loadErrorFileArray = [
        'inputs' => [
            'hidden' => [
                [
                    'name' => 'api-action',
                    'value' => 'get-error-file'
                ]
            ]
        ],
        'theme' => $theme,
        'action' => '/api/tools/get-error-file',
        'resultType' => 'html',
        'reloadOnSubmit' => false,
        'submitButton' => [
            'text' => 'Load Error File',
            'size' => 'medium',
        ],
    ];

    $clearErrorFileformArray = [
        'inputs' => [
            'hidden' => [
                [
                    'name' => 'api-action',
                    'value' => 'clear-error-file'
                ]
            ]
        ],
        'theme' => $theme,
        'action' => '/api/tools/clear-error-file',
        'resultType' => 'html',
        'reloadOnSubmit' => false,
        'submitButton' => [
            'text' => 'Clear Error File',
            'size' => 'medium',
        ],
    ];
    echo '<div class="flex space-x-2">';
        echo Forms::render($loadErrorFileArray);
        echo Forms::render($clearErrorFileformArray);
    echo '</div>';
}

// User sessions
echo Html::h2('Active User Sessions', true);

if (SESSION_STORAGE === 'database') {
    // Only show session management if using database sessions
    try {
        $sessionsModel = new \Models\Sessions();
        $activeCount = $sessionsModel->countActive();
        
        echo Alerts::info("Active sessions: <strong>$activeCount</strong>");
        
        // Button to load sessions
        $loadSessionsFormArray = [
            'inputs' => [
                'hidden' => [
                    [
                        'name' => 'api-action',
                        'value' => 'get-sessions'
                    ]
                ]
            ],
            'theme' => $theme,
            'action' => '/api/admin/sessions',
            'resultType' => 'html',
            'reloadOnSubmit' => false,
            'submitButton' => [
                'text' => 'Load Active Sessions',
                'size' => 'medium',
            ],
        ];
        
        // Button to clear all sessions
        $clearAllSessionsFormArray = [
            'inputs' => [
                'hidden' => [
                    [
                        'name' => 'api-action',
                        'value' => 'clear-all-sessions'
                    ]
                ]
            ],
            'theme' => $theme,
            'action' => '/api/admin/sessions',
            'resultType' => 'text',
            'reloadOnSubmit' => true,
            'confirm' => true,
            'confirmText' => 'Confirm Clear All Sessions except the current one?',
            'submitButton' => [
                'text' => 'Clear All Sessions',
                'size' => 'medium',
                'color' => 'red'
            ],
            'confirmMessage' => 'Are you sure you want to clear all sessions? This will log out all users.'
        ];
        
        echo '<div class="flex space-x-2 mb-4">';
            echo Forms::render($loadSessionsFormArray);
            echo Forms::render($clearAllSessionsFormArray);
        echo '</div>';
        
    } catch (\Exception $e) {
        echo Alerts::danger('Error loading sessions: ' . $e->getMessage());
    }
} else {
    echo Alerts::warning('Session management is only available when using database session storage. Current storage: <strong>' . SESSION_STORAGE . '</strong>');
}

// Display Server Info
$engine = DEFAULT_DATA_GRID_ENGINE;
$componentClass = "\\Components\\$engine";
echo $componentClass::fromData('Server Info (' . $engine . ')', $_SERVER, $theme);

echo Html::h2('PHP Info', true);

$phpInfoFormOptions = [
    'inputs' => [
        'hidden' => [
            [
                'name' => 'api-action',
                'value' => 'parse-phpinfo'
            ]
        ]
    ],
    'theme' => $theme,
    'action' => '/api/tools/php-info-parser',
    'resultType' => 'html',
    'reloadOnSubmit' => false,
    'submitButton' => [
        'text' => 'Get PHP Info',
        'size' => 'medium',
    ],
];

echo Forms::render($phpInfoFormOptions);
