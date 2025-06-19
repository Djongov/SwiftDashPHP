<?php

declare(strict_types=1);

use App\Authentication\Azure\AzureAD;
use App\Authentication\JWT;
use App\Authentication\AuthToken;
use Components\Html;
use Components\Forms;
use Components\Alerts;

$destinationUrl = $_GET['destination'] ?? '/';
if ($destinationUrl === '/logout') {
    $destinationUrl = '/';
}
if (AuthToken::get() !== null) {
    $idToken = JWT::parseTokenPayLoad(AuthToken::get());
    // Decide whether it is a local login or AzureAD login
    if ($idToken['iss'] === JWT_ISSUER) {
        // Check if valid
        if (JWT::checkToken(AuthToken::get())) {
            header('Location: ' . $destinationUrl);
            exit();
        }
    }
    // Check if valid
    if ($idToken['iss'] === 'https://login.microsoftonline.com/' . ENTRA_ID_TENANT_ID . '/v2.0' && AzureAD::check(AuthToken::get())) {
        header('Location: ' . $destinationUrl);
        exit();
    }
}

echo '<div class="flex items-center justify-center mx-4">';
    echo '<div class="flex flex-col w-full max-w-md my-16 px-4 py-6 rounded-lg ' . LIGHT_COLOR_SCHEME_CLASS . ' ' . DARK_COLOR_SCHEME_CLASS . ' sm:px-6 md:px-8 lg:px-10 border border-gray-300 shadow-md">';
        // Now the different external provider login options
if (ENTRA_ID_LOGIN || MICROSOFT_LIVE_LOGIN || GOOGLE_LOGIN) {
    echo Html::h3(translate('login_with_provider'), true, ['my-6']);
}
        // Azure
if (ENTRA_ID_LOGIN) {
    echo '<div class="flex gap-4 item-center">';
        echo '<a class="mb-4 w-full ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . ' font-medium text-center border border-gray-200 rounded-md shadow-sm hover:bg-gray-200 hover:dark:text-black" href="' . ENTRA_ID_LOGIN_BUTTON_URL . '">';
            echo '<div class="flex items-center justify-center py-3 px-3 leading-5">';
                echo MS_LOGO;
                    echo '<span class="ml-3">' . translate('login_with_msentraid') . '</span>';
            echo '</div>';
        echo '</a>';
    echo '</div>';
}
if (MICROSOFT_LIVE_LOGIN) {
    echo '<div class="flex gap-4 item-center">';
        echo '<a class="mb-4 w-full ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . ' font-medium text-center border border-gray-200 rounded-md shadow-sm hover:bg-gray-200 hover:dark:text-black" href="' . MS_LIVE_LOGIN_BUTTON_URL . '">';
            echo '<div class="flex items-center justify-center py-3 px-3 leading-5">';
                echo MS_LOGO;
                echo '<span class="ml-3">' . translate('login_with_mslive') . '</span>';
            echo '</div>';
        echo '</a>';
    echo '</div>';
}
        // Google
if (GOOGLE_LOGIN) {
    echo '<div class="flex gap-4 item-center">';
        echo '<a class="mb-4 w-full ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . ' font-medium text-center border border-gray-200 rounded-md shadow-sm hover:bg-gray-200 hover:dark:text-black" href="' . GOOGLE_LOGIN_BUTTON_URL . '">';
            echo '<div class="flex items-center justify-center py-3 px-3 leading-5">';
                echo GOOGLE_LOGO;
                echo '<span class="ml-3">' . translate('login_with_google') . '</span>';
            echo '</div>';
        echo '</a>';
    echo '</div>';
}
        // Local login
if (LOCAL_USER_LOGIN) {
    if (ENTRA_ID_LOGIN || GOOGLE_LOGIN || MICROSOFT_LIVE_LOGIN) {
        echo Html::p(translate('login_with_local_text'), ['text-center', 'mb-4']);
    } elseif (!ENTRA_ID_LOGIN && !GOOGLE_LOGIN && !MICROSOFT_LIVE_LOGIN) {
        echo Html::h3(translate('login_with_local_text'), true, ['my-6']);
    }
    $localLoginForm = [
        'inputs' => [
            'input' => [
                // Email
                [
                    'label' => translate('username'),
                    'type' => 'text',
                    'placeholder' => 'John.Doe@example.com',
                    'name' => 'username',
                    'required' => true,
                    'description' => 'Provide a valid username (email)'
                ],
                // Password
                [
                    'label' => translate('password'),
                    'type' => 'password',
                    'name' => 'password',
                    'required' => true,
                    'description' => 'Provide a valid password'
                ]
            ],
            'checkbox' => [
                [
                    'label' => translate('remember_me'),
                    'name' => 'remember',
                ]
            ],
            'hidden' => [
                [
                    'name' => 'state',
                    'value' => ($destinationUrl !== '/' && (substr($destinationUrl, 0, 1) !== '/' || !in_array($destinationUrl, ['/login', '/logout']))) ? '/' : $destinationUrl
                ]
            ]
        ],
        'theme' => $theme,
        'method' => 'POST',
        'action' => '/auth/local',
        'id' => 'local-login-form',
        'resultType' => 'text',
        'submitButton' => [
            'text' => translate('login'),
            'size' => 'medium',
        ],
    ];

    echo Forms::render($localLoginForm);

    echo '<script src="/assets/js/local-login.js"></script>';
}
        // If local login is enabled, show the registration link
if (LOCAL_USER_LOGIN) {
    // But if only show the link if manual registration is enabled
    if (MANUAL_REGISTRATION) {
        echo Html::small(translate('if_you_dont_have_account') . ' - <a class="underline" href="/register">' . translate('sign_up') . '</a>');
    } else {
        echo Html::small(translate('if_you_dont_have_account_contact_admin'));
    }
}
        // If no login methods are enabled, show an alert
if (!LOCAL_USER_LOGIN && !ENTRA_ID_LOGIN && !GOOGLE_LOGIN && !MICROSOFT_LIVE_LOGIN) {
    echo Alerts::danger(translate('no_logins_available'));
}
    // Close the login container
    echo '</div>';
// Close the flex container
echo '</div>';
