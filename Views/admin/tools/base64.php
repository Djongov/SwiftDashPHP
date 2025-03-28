<?php

declare(strict_types=1);

use Components\Forms;
use Components\Html;

echo Html::h1('Base64 Encode/Decode', true);

// Encode
$formOptionsEncode = [
    'inputs' => [
        'textarea' => [
            [
                'name' => 'encode',
                'label' => 'Data',
                'required' => true,
                'placeholder' => 'Data to encode',
                'description' => 'The data to encode'
            ]
        ]
    ],
    "action" => "/api/tools/base64",
    "resultType" => "html",
    "submitButton" => [
        "text" => "Encode",
        "size" => "medium",
    ]
];

// Let's wrap it
echo '<div class="container mx-auto">';
    echo Forms::render($formOptionsEncode, $theme);
echo '</div>';

// Decode
$formOptionsDecode = [
    'inputs' => [
        'textarea' => [
            [
                'name' => 'decode',
                'label' => 'Data',
                'required' => true,
                'placeholder' => 'Data to decode',
                'description' => 'The data to decode'
            ]
        ]
    ],
    "action" => "/api/tools/base64",
    "resultType" => "html",
    "submitButton" => [
        "text" => "Decode",
        "size" => "medium",
    ]
];

// Let's wrap it
echo '<div class="container mx-auto">';
    echo Forms::render($formOptionsDecode, $theme);
echo '</div>';
