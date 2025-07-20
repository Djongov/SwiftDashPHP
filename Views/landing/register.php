<?php

declare(strict_types=1);

use Components\Forms;
use Components\Html;
use Components\Alerts;

if (!LOCAL_USER_LOGIN) {
    echo Alerts::danger('Server is set to not allow local logins');
    return;
}

if (!MANUAL_REGISTRATION) {
    echo Alerts::danger(translate('manual_registration_disabled'));
    return;
}

// Registration form here
echo '<div class="flex items-center justify-center mx-4">
    <div class="flex flex-col w-full max-w-md my-16 px-4 py-8 rounded-lg ' . LIGHT_COLOR_SCHEME_CLASS . ' ' . DARK_COLOR_SCHEME_CLASS . ' sm:px-6 md:px-8 lg:px-10 border border-gray-300 shadow-md">';
        $registrationForm = [
            'inputs' => [
                'input' => [
                    // Email
                    [
                        'label' => translate('username'),
                        'type' => 'text',
                        'placeholder' => 'John84',
                        'name' => 'username',
                        'required' => true,
                        'description' => translate('usrname_description'),
                        'id' => uniqid(),
                    ],
                    [
                        'label' => translate('email'),
                        'type' => 'email',
                        'placeholder' => 'John.Doe@example.com',
                        'name' => 'email',
                        'required' => true,
                        'description' => translate('email_description'),
                        'id' => uniqid(),
                    ],
                    // Password
                    [
                        'label' => translate('password'),
                        'type' => 'password',
                        'name' => 'password',
                        'required' => true,
                        'description' => translate('password_description'),
                        'id' => uniqid(),
                    ],
                    // Password
                    [
                        'label' => translate('conrifm_password'),
                        'type' => 'password',
                        'name' => 'confirm_password',
                        'required' => true,
                        'description' => translate('confirm_password_description'),
                        'id' => uniqid(),
                    ],
                    // Name
                    [
                        'label' => translate('name'),
                        'type' => 'text',
                        'name' => 'name',
                        'required' => true,
                        'description' => translate('name_description'),
                        'id' => uniqid(),
                    ],
                ]
            ],
            'action' => '/api/user',
            'theme' => $theme, // Optional, defaults to COLOR_SCHEME
            'method' => 'POST', // Optional, defaults to POST
            'redirectOnSubmit' => '/login',
            'submitButton' => [
                'text' => translate('register'),
                'size' => 'medium',
                //'style' => '&#10060;'
            ],
        ];
        echo Html::h2(translate('register'), true);
        echo Html::small(translate('register_description'));
        echo Forms::render($registrationForm);
    echo '</div>';
echo '</div>';
