<?php declare(strict_types=1);

use Components\Forms;

$formOptions = [
    'inputs' => [
        'textarea' => [
            [
                'name' => 'data',
                'label' => 'Data',
                'required' => true,
                'placeholder' => 'Data to encode',
                'description' => 'The data to encode'
            ]
        ]
    ],
    "action" => "/api/tools/base64encode",
    "resultType" => "html",
    "submitButton" => [
        "text" => "Encode",
        "size" => "medium",
    ]
];

// Let's wrap it
echo '<div class="container mx-auto">';
    echo Forms::render($formOptions, $theme);
echo '</div>';
