<?php declare(strict_types=1);

use Components\Forms;
use Components\Html;
use Components\Alerts;
    
if (!LOCAL_USER_LOGIN) {
    echo Alerts::danger('Server is set to not allow local logins');
    return;
}

if (!MANUAL_REGISTRATION) {
    echo Alerts::danger('Server does not permit manual registration');
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
                        'label' => 'Username',
                        'type' => 'text',
                        'placeholder' => 'John84',
                        'name' => 'username',
                        'required' => true,
                        'description' => 'Provide a username',
                        'id' => uniqid(),
                    ],
                    [
                        'label' => 'Email',
                        'type' => 'email',
                        'placeholder' => 'John.Doe@example.com',
                        'name' => 'email',
                        'required' => true,
                        'description' => 'Provide a valid email',
                        'id' => uniqid(),
                    ],
                    // Password
                    [
                        'label' => 'Password',
                        'type' => 'password',
                        'name' => 'password',
                        'required' => true,
                        'description' => 'Provide a password',
                        'id' => uniqid(),
                    ],
                    // Password
                    [
                        'label' => 'Confirm Password',
                        'type' => 'password',
                        'name' => 'confirm_password',
                        'required' => true,
                        'description' => 'Confirm your password',
                        'id' => uniqid(),
                    ],
                    // Name
                    [
                        'label' => 'Name',
                        'type' => 'text',
                        'name' => 'name',
                        'required' => true,
                        'description' => 'Provide your name',
                        'id' => uniqid(),
                    ],
                ]
            ],
            'action' => '/api/user',
            'theme' => $theme, // Optional, defaults to COLOR_SCHEME
            'method' => 'POST', // Optional, defaults to POST
            'redirectOnSubmit' => '/login',
            'submitButton' => [
                'text' => 'Register',
                'size' => 'medium',
                //'style' => '&#10060;'
            ],
        ];
        echo Html::h2('Register', true);
        echo Html::small('Register a new local user account.');
        echo Forms::render($registrationForm);
    echo '</div>';
echo '</div>';

