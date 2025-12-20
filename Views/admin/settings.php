<?php

declare(strict_types=1);

use Components\Forms;
use Components\Html;
use Components\Alerts;
use Models\AppSettings;

$appSettings = new AppSettings();

echo Html::h2('Edit App Settings', true, ['mb-4', 'mt-8']);

try {
    echo \Components\AppSetting::renderSettings('system', $theme);
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
            [
                'type'        => 'text',
                'name'        => 'owner',
                'label'       => 'Setting Owner',
                'placeholder' => 'Enter setting owner',
                'value'       => $usernameArray['username'] ?? 'system',
                'required'    => false,
            ],
            [
                'type'        => 'text',
                'name'        => 'description',
                'label'       => 'Setting Description',
                'placeholder' => 'Enter setting description',
                'required'    => false,
            ]
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

echo Html::h2('Edit SMTP Settings', true, ['mb-4', 'mt-8']);

try {
    echo \Components\AppSetting::renderSettings('smtp', $theme);
} catch (Exception $e) {
    echo Alerts::danger($e->getMessage());
}

echo Html::h2('Create New App Setting', true, ['mb-4', 'mt-8']);

echo Html::divBox(Forms::render($formOptions, $theme));
