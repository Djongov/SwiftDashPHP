<?php

declare(strict_types=1);

use App\Security\Firewall;
use App\Api\Response;
use Components\Forms;
use Components\Html;

// First firewall check
Firewall::activate();

// Admin check
if (!$isAdmin) {
    Response::output('You are not an admin', 403);
}
// /api/admin/db-system-migrate or /api/admin/db-project-migrate
$formOptions = [
    'inputs' => [],
    'theme' => $theme,
    'action' => '/api/admin/db-migrate',
    'confirm' => true,
    'confirmText' => 'Are you sure you want to run the migration? This action cannot be undone.',
    'submitButton' => [
        'text' => 'Run',
        'size' => 'small'
    ]
];

$formTypes = ['system', 'project'];

foreach ($formTypes as $formType) {
    $formOptions['inputs']['hidden'] = [
        [
                'name' => 'migrate_type',
                'value' => $formType
        ]
    ];

    echo Html::divBox(Html::h2('Run ' .  $formType . ' Migration') . Forms::render($formOptions, $theme));
}


