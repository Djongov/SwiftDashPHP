<?php

declare(strict_types=1);

use Components\Forms;
use Components\Html;
use Components\Alerts;
use Models\AppSettings;

$appSettings = new AppSettings();
$allAppSettings = $appSettings->getAllByOwner('system');
$settingNames = array_map(fn($s) => $s['name'], $allAppSettings);

if (empty($allAppSettings)) {
    echo Alerts::info('No App Settings found. You can create one below.');
    return;
}

echo Html::h2('Edit App Settings', true, ['mb-4', 'mt-8']);

try {
    echo \Components\AppSetting::renderSettings($settingNames, $theme);
} catch (Exception $e) {
    echo Alerts::danger($e->getMessage());
}

$allowedTypes = ['string', 'int', 'bool', 'float', 'json'];

$allowedTypesOptions = [];

foreach ($allowedTypes as $type) {
    $allowedTypesOptions[] = [
        'value' => $type,
        'text' => $type,
    ];
}

$formOptions = [
    'inputs' => [
        'input' => [
            [
                'type'        => 'text',
                'name'        => 'name',
                'label'       => 'Setting Name',
                'placeholder' => 'Enter setting name',
                'required'    => true,
            ],
            [
                'type'        => 'text',
                'name'        => 'value',
                'label'       => 'Setting Value',
                'placeholder' => 'Enter setting value',
                'required'    => true,
            ],
        ],
        'select' => [
            [
                'name'     => 'type',
                'label'    => 'Setting Type',
                'options'  => $allowedTypesOptions,
                'required' => true,
            ],
        ],
    ],
    'theme' => $theme,
    'action' => '/api/app-settings',
    'method' => 'POST',
    'submitButton' => [
        'text' => 'Create',
    ]
];
echo Html::h2('Create New App Setting', true, ['mb-4', 'mt-8']);

echo Html::divBox(Forms::render($formOptions, $theme));

echo \Components\DataGrid::fromDBTable('app_settings', 'App Settings', $theme);
