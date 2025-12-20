<?php

declare(strict_types=1);

use Components\Forms;
use Components\Html;

$formOptions = [
    "inputs" => [
        'input' => [
            [
                'type' => 'text',
                'label' => 'Recipients',
                'name' => 'recipients',
                'placeholder' => 'John.Doe@example.com;Lilith.Sambers@example.com',
                'description' => 'Enter the email addresses of the recipients, separated by a semicolon.',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => 'Subject',
                'name' => 'subject',
                'placeholder' => 'Subject',
                'description' => 'Enter the subject of the email.',
                'required' => true,
            ],
        ],
        "tinymce" => [
            [
                "label" => "Email Body",
                "name" => "body",
                "description" => "Enter the message of the email. Can be in HTML format.",
                "required" => true,
            ]
        ],
        "hidden" => [
            [
                "name" => "hidden",
                "value" => "hidden value",
            ]
        ],
    ],
    "id" => "tinymce",
    "action" => "/api/mail/smtp",
    "submitButton" => [
        "text" => "Send"
    ]
];

// Let's wrap it
echo '<div class="container mx-auto">';
    echo Html::h2('Mailer (SMTP)', true);
    echo Html::p('Send an email via SMTP and TinyMCE as editor.', ['text-center']);
    echo Forms::render($formOptions);
echo '</div>';

echo '<script src="' . TINYMCE_SCRIPT_LINK . '" referrerpolicy="origin"></script>';
echo '<script src="/assets/js/mailer.js?=' . time() . '"></script>';
