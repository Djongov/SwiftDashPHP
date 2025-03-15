<?php declare(strict_types=1);

namespace Components\Page;

use Components\Forms;
use Components\Html;

class CookieBanner {
    public static function render(string $theme) : string
    {
        // If cookie conset is already set, don't show the banner
        if (isset($_COOKIE['cookie-consent']) && $_COOKIE['cookie-consent'] === 'accept') {
            return '';
        }
        $listOfCookies = [
            '<b>session cookie</b> - This cookie is essential for the website to function properly. They are used to maintain the session and remember language settings.',
            '<b>consent cookie</b> - This cookie is used to remember the user\'s choice about cookies on the website. Where users have previously indicated a preference, that user\'s preference will be stored in this cookie.',
        ];
        $cookieListHtml = '';
        $cookieListHtml .= Html::ul($listOfCookies);
        $acceptFormOptions = [
            "inputs" => [
                "hidden" => [
                    [
                        "name" => "consent",
                        "value" => "accept",
                    ]
                ],
            ],
            'theme' => $theme,
            'reloadOnSubmit' => true,
            "action" => "/api/cookie-consent",
            "submitButton" => [
                "text" => "Accept",
                "size" => "medium",
            ]
        ];
        return '
        <div id="cookie-banner" class="z-50 fixed bottom-5 left-1/2 transform -translate-x-1/2 border border-gray-700 dark:border-gray-400 bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white p-4 rounded-lg shadow-lg max-w-md">
            <p class="text-sm">We use cookies to enhance your experience. By continuing to use our site, you agree to our use of cookies.</p>
            ' . $cookieListHtml . '
            <div class="mt-3 flex items-center justify-start space-x-3">
                <div>' . Forms::render($acceptFormOptions) . '</div>
                <div>' . Html::smallButtonLink('https://google.com', 'Leave', 'red') . '</div>
                <div>' . Html::a('Learn more', '/privacy-policy', $theme) . '</div>
            </div>
        </div>';
    }
}
