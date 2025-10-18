<?php

declare(strict_types=1);

namespace Components;

use Components\Html;
use App\Security\CSRF;

class LanguageSwitcher
{
    public static function render($theme): string
    {
        // Generate unique ID to prevent conflicts when multiple instances are rendered
        $uniqueId = 'lang-' . uniqid();
        
        // Start the form HTML
        $html = '';
        $html .= '<form class="select-submitter" data-reload="true" method="POST" action="/api/set-lang">';
            $html .= '<select id="' . $uniqueId . '" name="lang" class="' . Html::selectInputClasses($theme) . '">';

            // Define language options
            $languages = [
                'en' => 'English',
                'bg' => 'Български', // Bulgarian
            ];
            // Loop through languages and set the selected one
            foreach ($languages as $code => $language) {
                $selected = ($_SESSION['lang'] ?? DEFAULT_LANG) === $code ? 'selected' : '';
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
