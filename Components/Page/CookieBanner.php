<?php

declare(strict_types=1);

namespace Components\Page;

use Components\Forms;
use Components\Html;

class CookieBanner
{
    public static function render(string $theme): string
    {
        // If cookie consent is already set, don't show the banner
        if (isset($_COOKIE['cookie-consent']) && $_COOKIE['cookie-consent'] === 'accept') {
            return '';
        }

        $listOfCookies = [
            translate('session_cookie'),
            translate('consent_cookie'),
        ];

        $cookieListHtml = Html::ul($listOfCookies);

        $acceptFormOptions = [
            'inputs' => [
                'hidden' => [
                    [
                        'name' => 'consent',
                        'value' => 'accept',
                    ]
                ],
            ],
            'theme' => $theme,
            'reloadOnSubmit' => true,
            'action' => '/api/cookie-consent',
            'submitButton' => [
                'text' => translate('cookie_consent_accept_button'),
                'size' => 'medium',
            ]
        ];

        return '
        <div id="cookie-banner" class="z-50 fixed bottom-5 left-1/2 transform -translate-x-1/2 border border-gray-700 dark:border-gray-400 bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white p-4 rounded-lg shadow-lg max-w-md">
            <p class="text-sm">' . translate('cookie_consent_welcome_text') . '</p>
            ' . $cookieListHtml . '
            <div class="mt-3 flex items-center justify-start space-x-3">
                <div>' . Forms::render($acceptFormOptions) . '</div>
                <div>' . Html::smallButtonLink('https://google.com', translate('cookie_consent_decline_button'), 'red') . '</div>
                <div>' . Html::a(translate('cookie_consent_learn_more_button'), '/privacy-policy', $theme) . '</div>
            </div>
        </div>';
    }
}
