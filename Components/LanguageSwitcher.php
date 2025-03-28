<?php

declare(strict_types=1);

namespace Components;

use Components\Html;
use App\Security\CSRF;

class LanguageSwitcher
{
    public static function render($theme): string
    {
        // Start the form HTML
        $html = '';
        $html .= '<form class="select-submitter" data-reload="true" method="POST" action="/api/set-lang">';
            $html .= '<select id="lang" name="lang" class="' . Html::selectInputClasses($theme) . '">';

            // Define language options
            $languages = [
                'en' => 'English',
                'bg' => 'Български', // Bulgarian
            ];

            function getLanguageFlag($code)
            {
                $flags = [
                    'en' => '🇬🇧',  // UK flag directly as an emoji
                    'bg' => '🇧🇬',  // Bulgaria flag directly as an emoji (corrected)
                ];

                return $flags[$code] ?? '';  // Returns the flag for the language code, or an empty string if not found
            }
            // Loop through languages and set the selected one
            foreach ($languages as $code => $language) {
                $selected = ($_SESSION['lang'] ?? 'en') === $code ? 'selected' : '';
                $html .= '<option value="' . $code . '" ' . $selected . '>' . getLanguageFlag($code) . ' ' . $language . '</option>';
            }

            // Close the select dropdown
            $html .= '</select>';

            // CSRF token for security
            $html .= CSRF::createTag();
            $html .= '</form>';

            return $html;
    }
}
