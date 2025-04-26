<?php

declare(strict_types=1);

namespace Components;

class Html
{
    public static function h1($text, $center = false, $extraClasses = []): string
    {
        if ($center) {
            array_push($extraClasses, 'text-center');
        }
        if (!$extraClasses) {
            return '<h1 class="mx-2 my-2 text-2xl md:text-3xl lg:text-4xl font-bold leading-none tracking-tight ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . '">' . $text . '</h1>';
        } else {
            return '<h1 class="mx-2 my-2 text-2xl md:text-3xl lg:text-4xl font-bold leading-none tracking-tight ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . ' ' . implode(' ', $extraClasses) . '">' . $text . '</h1>';
        }
    }
    public static function h2($text, $center = false, $extraClasses = []): string
    {
        if ($center) {
            array_push($extraClasses, 'text-center');
        }

        if (!$extraClasses) {
            return '<h2 class="mx-2 my-2 text-xl md:text-2xl lg:text-3xl font-bold leading-none tracking-tight ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . '">' . $text . '</h2>';
        } else {
            return '<h2 class="mx-2 my-2 text-xl md:text-2xl lg:text-3xl font-bold leading-none tracking-tight ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . ' ' . implode(' ', $extraClasses) . '">' . $text . '</h2>';
        }
    }
    public static function h3($text, $center = false, $extraClasses = []): string
    {
        if ($center) {
            array_push($extraClasses, 'text-center');
        }
        if (!$extraClasses) {
            return '<h3 class="mx-2 my-2 text-md md:text-md lg:text-xl font-bold ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . ' break-words">' . $text . '</h3>';
        } else {
            return '<h3 class="mx-2 my-2 text-md md:text-md lg:text-xl font-bold ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . ' break-words ' . implode(' ', $extraClasses) . '">' . $text . '</h3>';
        }
    }
    public static function h4($text, $center = false, $extraClasses = []): string
    {
        if ($center) {
            array_push($extraClasses, 'text-center');
        }
        if (!$extraClasses) {
            return '<h4 class="mx-2 my-2 text-md font-bold ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . ' break-words">' . $text . '</h4>';
        } else {
            return '<h4 class="mx-2 my-2 text-md font-bold ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . ' break-words ' . implode(' ', $extraClasses) . '">' . $text . '</h4>';
        }
    }
    public static function h5($text, $center = false, $extraClasses = []): string
    {
        if ($center) {
            array_push($extraClasses, 'text-center');
        }
        if (!$extraClasses) {
            return '<h5 class="mx-2 my-2 text-sm font-bold ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . ' break-words">' . $text . '</h5>';
        } else {
            return '<h5 class="mx-2 my-2 text-sm font-bold ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . ' break-words ' . implode(' ', $extraClasses) . '">' . $text . '</h5>';
        }
    }
    // Anchor
    public static function a($text, $href, $theme, $target = '_self', $extraClasses = []): string
    {
        if (!$extraClasses) {
            return '<a href="' . $href . '" target="' . $target . '" class="text-' . $theme . '-500 hover:underline dark:text-' . $theme . '-400">' . $text . '</a>';
        } else {
            return '<a href="' . $href . '" target="' . $target . '" class="text-' . $theme . '-500 hover:underline dark:text-' . $theme . '-400 ' . implode(' ', $extraClasses) . '">' . $text . '</a>';
        }
    }
    public static function p(string $text, array $extraClasses = []): string
    {
        $defaultClasses = [
            'break-words',
            'm-2'
        ];
        $classes = array_merge($defaultClasses, $extraClasses);

        return '<p class="' . implode(' ', $classes) . '">' . $text . '</p>';
    }
    public static function small($text, $extraClasses = []): string
    {
        if (!$extraClasses) {
            return '<small class="my-2 break-words text-center">' . $text . '</small>';
        } else {
            return '<small class="my-2 break-words text-center ' . implode(' ', $extraClasses) . '">' . $text . '</small>';
        }
    }
    public static function warningParagraph($text, $extraClasses = []): string
    {
        if (!$extraClasses) {
            return '<p class="mx-2 my-2 text-red-500 font-semibold">' . $text . '</p>';
        } else {
            return '<p class="mx-2 my-2 text-red-500 font-semibold ' . implode(' ', $extraClasses) . '">' . $text . '</p>';
        }
    }
    public static function divBox($content, $extraClasses = []): string
    {
        if (!$extraClasses) {
            return '<div class="p-4 m-4 max-w-lg ' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700">' . $content . '</div>';
        } else {
            return '<div class="p-4 m-4 max-w-lg ' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700 ' . implode(' ', $extraClasses) . '">' . $content . '</div>';
        }
    }
    /* Form elements */
    public static function input(string $size, string $type, ?string $id, string $name, string $title, mixed $value, string $placeholder, string $description, string $label_name, string $theme, bool $disabled, bool $required, bool $readOnly, bool $encased = true, ?int $min = null, ?int $max = null, float|int|null $step = null, $pattern = '', $extraClasses = [], $dataAttributes = []): string
    {
        if ($disabled || $readOnly) {
            $theme = 'red';
            array_push($extraClasses, 'cursor-not-allowed', 'border-red-500', 'dark:border-red-600');
        }
        // Check allowed types
        $allowedTypes = ['text', 'number', 'email', 'password', 'search', 'datetime-local', 'tel', 'file'];
        if (!in_array($type, $allowedTypes)) {
            throw new \Exception('Invalid type for input. Needs to be one of: ' . implode(', ', $allowedTypes) . '.');
        }
        // Check if sizes are valid
        $sizes = ['small', 'default', 'large'];
        if (!in_array($size, $sizes)) {
            throw new \Exception('Invalid size for input. Needs to be one of: ' . implode(', ', $sizes) . '.');
        }
        // Let's figure out the width of the input
        if ($type === 'number') {
            $width = 'w-24';
        } else {
            $width = 'w-full';
        }
        // Optinally add the min/max values
        $minMaxString = '';
        if ($type === 'number') {
            // Set min max values if provided
            $min = (isset($min) && $min !== null ) ? $min : null;
            $max = (isset($max) && $max !== null) ? $max : null;
            if ($min !== null) {
                $minMaxString .= 'min="' . $min . '" ';
            }
            if ($max !== null) {
                $minMaxString .= 'max="' . $max . '" ';
            }
        }
        $stepString = '';
        if ($type === 'number') {
            if ($step !== null) {
                $step = (isset($step) && $step !== null) ? $step : null;
                $stepString .= 'step="' . $step . '" ';
            }
        }

        if ($id == null) {
            $id = uniqid();
        }

        $inputClasses = [
            BODY_COLOR_SCHEME_CLASS,
            'border',
            'border-gray-300',
            TEXT_COLOR_SCHEME,
            'text-sm',
            'rounded-lg',
            'focus:ring-' . $theme . '-500',
            'focus:border-' . $theme . '-500',
            'block',
            $width,
            'p-2.5',
            BODY_DARK_COLOR_SCHEME_CLASS,
            'dark:border-gray-600',
            'dark:placeholder-gray-400',
            TEXT_DARK_COLOR_SCHEME,
            'dark:focus:ring-' . $theme . '-500',
            'dark:focus:border-' . $theme . '-500',
            'outline-none',
        ];

        // Extra classes now
        $inputClasses = array_merge($inputClasses, $extraClasses);

        // First get some of the meta data
        $disabled = $disabled ? 'disabled' : '';
        $requiredOriginal = $required;
        $required = $required ? 'required' : '';
        $readOnly = $readOnly ? 'readonly' : '';
        $value = ($value == '') ? '' : ' value="' . $value . '"';
        $placeholder = ($placeholder === '') ? '' : 'placeholder="' . $placeholder . '"';
        $extraClasses = implode(' ', $extraClasses);
        $pattern = ($pattern === '') ? '' : 'pattern="' . $pattern . '"';

        // Title
        if ($title === '') {
            $title = ($label_name !== '') ? 'title="' . $label_name . '"' : 'title="' . $name . '"';
        } else {
            $title = 'title="' . $title . '"';
        }

        // Data attributes
        $dataAttributesString = '';
        if (!empty($dataAttributes)) {
            foreach ($dataAttributes as $key => $v) {
                $dataAttributesString .= 'data-' . $key . '="' . $v . '" ';
            }
        }
        // handle file

        if ($type === 'file') {
            if ($encased) {
                $html = '';
                $html .= '<div class="my-4">';
                    $html .= ($label_name !== '') ? self::label($id, $label_name, $requiredOriginal) : '';
                    $html .= '<input id="' . $id . '" type="' . $type . '" name="' . $name . '" class="' . implode(' ', $inputClasses) . '" ' . $placeholder . ' ' . $required . ' ' . $disabled . ' ' . $readOnly . ' ' . $value . $pattern . ' ' . $title . $minMaxString . $stepString . $dataAttributesString . ' autocomplete="on" />';
                    $html .= ($description !== '') ? '<p class="mt-2 text-sm text-gray-500 dark:text-gray-400">' . $description . '</p>' : '';
                $html .= '</div>';
                return $html;
            } else {
                return '<input id="' . $id . '" type="' . $type . '" name="' . $name . '" class="' . implode(' ', $inputClasses) . '" ' . $placeholder . ' ' . $required . ' ' . $disabled . ' ' . $readOnly . ' ' . $value . $pattern . ' ' . $title . $minMaxString . $stepString . $dataAttributesString . ' autocomplete="on" />';
            }
        }
        $inputHtml = '<input id="' . $id . '" type="' . $type . '" name="' . $name . '" class="' . implode(' ', $inputClasses) . '" ' . $placeholder . ' ' . $required . ' ' . $disabled . ' ' . $readOnly . ' ' . $value . $pattern . ' ' . $title . $minMaxString . $stepString . $dataAttributesString . ' autocomplete="on" />';

        $html = '';
        if ($encased) {
            $html .= '<div class="my-4">';
            if ($label_name !== '') {
                $html .= self::label($id, $label_name, $requiredOriginal);
            }
                $html .= $inputHtml;
                $html .= ($description !== '') ? '<p class="mt-2 text-sm text-gray-500 dark:text-gray-400">' . $description . '</p>' : '';
            $html .= '</div>';
        } else {
            $html .= $inputHtml;
        }

        return $html;
    }
    public static function textArea(
        ?string $id,
        string $name,
        string $value,
        string $placeholder,
        string $title,
        string $description,
        string $label_name,
        string $theme,
        bool $disabled,
        bool $required,
        bool $readonly,
        int $rows,
        int $cols,
        array $extraClasses = [],
        array $dataAttributes = []
    ): string
    {
        // Generate unique ID if not provided
        $id ??= uniqid();

        // Prepare attributes
        $attributes = self::buildAttributes(
            [
            'id' => $id,
            'name' => $name,
            'placeholder' => $placeholder ?: null,
            'title' => $title ?: ($label_name ?: $name),
            'class' => self::buildInputClasses($theme, $extraClasses),
            'rows' => $rows,
            'cols' => $cols,
            'disabled' => $disabled ? 'disabled' : null,
            'required' => $required ? 'required' : null,
            'readonly' => $readonly ? 'readonly' : null,
            ], $dataAttributes
        );

        // Build textarea HTML
        $textareaHtml = sprintf(
            '<textarea %s>%s</textarea>',
            $attributes,
            htmlspecialchars($value)
        );

        // Build outer container with label and description
        return sprintf(
            '<div class="my-4">%s%s%s</div>',
            $label_name ? self::label($id, $label_name, $required) : '',
            $textareaHtml,
            $description ? sprintf('<p class="mt-2 text-sm ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . '">%s</p>', $description) : ''
        );
    }

    private static function buildAttributes(array $attributes, array $dataAttributes = []): string
    {
        // Merge regular attributes and data attributes
        $mergedAttributes = array_merge(
            array_filter($attributes, fn($value) => $value !== null),
            array_map(fn($key, $value) => "data-$key=\"$value\"", array_keys($dataAttributes), $dataAttributes)
        );

        // Build a string of attributes
        return implode(
            ' ', array_map(
                fn($key, $value) => is_numeric($key) ? $value : "$key=\"$value\"",
                array_keys($mergedAttributes),
                $mergedAttributes
            )
        );
    }

    private static function buildInputClasses(string $theme, array $extraClasses): string
    {
        // Core input classes
        $baseClasses = [
            'w-full',
            'p-2',
            'text-sm',
            BODY_COLOR_SCHEME_CLASS,
            BODY_DARK_COLOR_SCHEME_CLASS,
            'appearance-none',
            'border-2',
            'border-gray-100',
            'rounded-lg',
            LIGHT_COLOR_SCHEME_CLASS,
            'leading-tight',
            'focus:outline-none',
            "focus:" . BODY_COLOR_SCHEME_CLASS,
            "focus:border-$theme-500",
            'dark:border-gray-600',
            'dark:placeholder-gray-400',
            TEXT_DARK_COLOR_SCHEME,
            "dark:focus:ring-$theme-500",
            "dark:focus:border-$theme-500"
        ];

        // Combine with extra classes
        return implode(' ', array_merge($baseClasses, $extraClasses));
    }
    public static function checkbox(
        ?string $id,
        string $name,
        string $value,
        string $label,
        ?string $description,
        bool $required,
        bool $checked,
        bool $disabled,
        bool $readOnly,
        string $theme,
        array $extraClasses = []
    ): string
    {
        // Generate a unique ID if not provided
        $id = $id ?: uniqid($name);

        // Prepare description with popover if provided
        $descriptionHtml = '';
        if (!empty($description)) {
            $descriptionHtml = <<<HTML
    <i data-popover-target="{$id}-info" class="cursor-pointer ml-1 rounded-full border border-gray-300">
        i
        <div data-popover id="{$id}-info" role="tooltip" class="absolute z-10 invisible inline-block w-64 text-sm font-light text-gray-500 transition-opacity duration-300 bg-white border border-gray-200 rounded-lg shadow-sm opacity-0 dark:text-gray-400 dark:border-gray-600 {BODY_DARK_COLOR_SCHEME_CLASS}">
            <div class="px-3 py-2">
                <p>{$description}</p>
            </div>
        </div>
    </i>
    HTML;
        }

        // Convert boolean attributes to HTML attributes
        $attributes = [
            'disabled' => $disabled ? 'disabled' : '',
            'readonly' => $readOnly ? 'readonly' : '',
            'checked' => $checked ? 'checked' : '',
            'required' => $required ? 'required' : '',
        ];

        $attributeString = implode(' ', array_filter($attributes));

        // Extra classes
        $extraClassesString = !empty($extraClasses) ? implode(' ', $extraClasses) : '';

        // Checkbox input and label HTML
        return <<<HTML
    <div class="mt-2">
        <div class="flex items-center mb-4">
            <input 
                id="{$id}" 
                name="{$name}" 
                title="{$name}" 
                type="checkbox" 
                value="{$value}" 
                class="w-4 h-4 text-{$theme}-600 bg-gray-100 rounded border-gray-300 focus:ring-{$theme}-500 dark:focus:ring-{$theme}-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 {$extraClassesString}" 
                {$attributeString} 
            />
            <label for="{$id}" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                {$label}
                {$descriptionHtml}
            </label>
        </div>
    </div>
    HTML;
    }
    public static function label(string $id, string $label_name, bool $required): string
    {
        $requiredIndicator = $required
            ? '<span class="text-red-500"> *</span>'
            : '';

        $title = $required
            ? 'title="required ' . htmlspecialchars($label_name) . ' field"'
            : '';

        $textColorScheme = TEXT_COLOR_SCHEME;
        $textDarkColorScheme = TEXT_DARK_COLOR_SCHEME;

        return <<<HTML
        <label {$title} for="{$id}" class="block my-2 text-sm font-medium {$textColorScheme} {$textDarkColorScheme}">
            {$label_name}{$requiredIndicator}
        </label>
        HTML;
    }
    public static function code(string $text, string $codeTitle = '', array $classes = []): string
    {
        $classString = implode(' ', $classes);
        $titleHtml = $codeTitle !== ''
            ? '<p class="font-bold ' . $classString . '">' . htmlspecialchars($codeTitle) . '</p>'
            : '';

        $lightColorSchemeClass = LIGHT_COLOR_SCHEME_CLASS;
        $darkColorSchemeClass = DARK_COLOR_SCHEME_CLASS;
        return <<<HTML
    <pre class="p-4 m-4 max-w-fit overflow-auto {$lightColorSchemeClass} rounded-lg border border-gray-200 shadow-md {$darkColorSchemeClass} dark:border-gray-700 break-words">
        {$titleHtml}
        <code class="c0py">{$text}</code>
    </pre>
    HTML;
    }
    public static function horizontalLine(): string
    {
        return '<hr class="my-2 border-gray-300 dark:border-gray-700">';
    }
    public static function selectInputClasses($theme): string
    {
        return 'ml-2 p-1 text-sm text-gray-900 border outline-none border-gray-300 rounded ' . BODY_COLOR_SCHEME_CLASS . ' ' . BODY_DARK_COLOR_SCHEME_CLASS . ' focus:ring-' . $theme . '-500 focus:border-' . $theme . '-500  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-' . $theme . '-500 dark:focus:border-' . $theme . '-500';
    }
    public static function badge($text, $theme): string
    {
        return '<span class="inline-block py-px px-2 mb-4 text-xs leading-5 text-gray-900 bg-' . $theme . '-200 font-medium uppercase rounded-full shadow-sm">' . $text . '</span>';
    }
    public static function waveSeparator($theme, $color_strength_one, $color_strength_two, $color_strength_three): string
    {
        $html = '<div class="w-full">';
        $html .= '<svg viewBox="0 0 1428 154" version="1.1" xmlns="http://www.w3.org/2000/svg"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g class="fill-' . $theme . '-' . $color_strength_one . '" fill-rule="nonzero"><path d="M0,0 C90.7283404,0.927527913 147.912752,27.187927 291.910178,59.9119003 C387.908462,81.7278826 543.605069,89.334785 759,82.7326078 C469.336065,156.254352 216.336065,153.6679 0,74.9732496" opacity="0.3"></path><path d="M100,104.708498 C277.413333,72.2345949 426.147877,52.5246657 546.203633,45.5787101 C666.259389,38.6327546 810.524845,41.7979068 979,55.0741668 C931.069965,56.122511 810.303266,74.8455141 616.699903,111.243176 C423.096539,147.640838 250.863238,145.462612 100,104.708498 Z" opacity="0.3"></path><path d="M1046,51.6521276 C1130.83045,29.328812 1279.08318,17.607883 1439,40.1656806 L1439,120 C1271.17211,77.9435312 1140.17211,55.1609071 1046,51.6521276 Z" id="Path-4" class="fill-' . $theme . '-' . $color_strength_two . '"></path></g><g transform="translate(-4, 60)" class="fill-' . $theme . '-' . $color_strength_three . '" fill-rule="nonzero"><path d="M0.457,34.035 C57.086,53.198 98.208,65.809 123.822,71.865 C181.454,85.495 234.295,90.29 272.033,93.459 C311.355,96.759 396.635,95.801 461.025,91.663 C486.76,90.01 518.727,86.372 556.926,80.752 C595.747,74.596 622.372,70.008 636.799,66.991 C663.913,61.324 712.501,49.503 727.605,46.128 C780.47,34.317 818.839,22.532 856.324,15.904 C922.689,4.169 955.676,2.522 1011.185,0.432 C1060.705,1.477 1097.39,3.129 1121.236,5.387 C1161.703,9.219 1208.621,17.821 1235.4,22.304 C1285.855,30.748 1354.351,47.432 1440.886,72.354 L1441.191,104.352 L1.121,104.031 L0.457,14.035 Z"></path></g></g></svg>';
        $html .= '</div>';
        return $html;
    }
    public static function waveSeparatorLight($theme, $color_strength_one): string
    {
        $html = '<div class="w-full">';
        $html .= '<svg viewBox="0 0 1428 154" version="1.1" xmlns="http://www.w3.org/2000/svg"><title>background waves</title><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g transform="translate(-4, 60)" class="fill-' . $theme . '-' . $color_strength_one . '" fill-rule="nonzero"><path d="M0.457,34.035 C57.086,53.198 98.208,65.809 123.822,71.865 C181.454,85.495 234.295,90.29 272.033,93.459 C311.355,96.759 396.635,95.801 461.025,91.663 C486.76,90.01 518.727,86.372 556.926,80.752 C595.747,74.596 622.372,70.008 636.799,66.991 C663.913,61.324 712.501,49.503 727.605,46.128 C780.47,34.317 818.839,22.532 856.324,15.904 C922.689,4.169 955.676,2.522 1011.185,0.432 C1060.705,1.477 1097.39,3.129 1121.236,5.387 C1161.703,9.219 1208.621,17.821 1235.4,22.304 C1285.855,30.748 1354.351,47.432 1440.886,72.354 L1441.191,104.352 L1.121,104.031 L0.457,14.035 Z"></path></g></g></svg>';
        $html .= '</div>';
        return $html;
    }
    public static function waveSeparatorLeft($theme, $color_strength_one): string
    {
        $html = '<div class="w-full">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 160" ><g _ngcontent-ccg-c38=""><g data-name="background shapes"><path class="fill-' . $theme . '-' . $color_strength_one . '" opacity="0.3" d="M1001.3,183.07c-477.77,0-702.14-122.79-949.95-122.79Q24.46,60.28,0,61V193.12H1024V182.93Q1012.81,183.07,1001.3,183.07Z"></path></g></g></svg>';
        $html .= '</div>';
        return $html;
    }

    public static function waveSeparatorBottom($theme, $color_strength_one, $flipped = false): string
    {
        $html = '<div class="w-full">';
        if ($flipped) {
            $flip_class = 'class="transform -scale-y-100"';
        } else {
            $flip_class = '';
        }
        $html .= '<svg ' . $flip_class . ' viewBox="0 0 1428 154" version="1.1" xmlns="http://www.w3.org/2000/svg"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g transform="translate(-4, 60)" class="fill-' . $theme . '-' . $color_strength_one . '" fill-rule="nonzero"><path d="M0.457,34.035 C57.086,53.198 98.208,65.809 123.822,71.865 C181.454,85.495 234.295,90.29 272.033,93.459 C311.355,96.759 396.635,95.801 461.025,91.663 C486.76,90.01 518.727,86.372 556.926,80.752 C595.747,74.596 622.372,70.008 636.799,66.991 C663.913,61.324 712.501,49.503 727.605,46.128 C780.47,34.317 818.839,22.532 856.324,15.904 C922.689,4.169 955.676,2.522 1011.185,0.432 C1060.705,1.477 1097.39,3.129 1121.236,5.387 C1161.703,9.219 1208.621,17.821 1235.4,22.304 C1285.855,30.748 1354.351,47.432 1440.886,72.354 L1441.191,104.352 L1.121,104.031 L0.457,14.035 Z"></path></g></g></svg>';
        $html .= '</div>';
        return $html;
    }
    public static function bigButtonLink($link, $text, $theme, $extraClasses = []): string
    {
        return '<a class="inline-flex items-center justify-center px-7 py-3 h-14 w-full md:w-auto mb-2 md:mb-0 md:mr-4 text-lg leading-7 text-' . $theme . '-50 bg-' . $theme . '-500 hover:bg-' . $theme . '-600 font-medium focus:ring-2 focus:ring-' . $theme . '-500 focus:ring-opacity-50 border border-transparent rounded-md shadow-sm ' . implode(' ', $extraClasses) . '" href="' . $link . '">' . $text . '</a>';
    }
    public static function mediumButtonLink($link, $text, $theme, $extraClasses = []): string
    {
        return '<a class="inline-flex items-center justify-center ml-2 px-2 py-2 h-10 w-32 md:w-44 mb-2 text-lg leading-7 text-' . $theme . '-50 bg-' . $theme . '-500 hover:bg-' . $theme . '-600 font-medium focus:ring-2 focus:ring-' . $theme . '-500 focus:ring-opacity-50 border border-transparent rounded-md shadow-sm ' . implode(' ', $extraClasses) . '" href="' . $link . '">' . $text . '</a>';
    }
    public static function smallButtonLink($link, $text, $theme, $extraClasses = []): string
    {

        return '<a class="px-2 py-1 text-md inline-flex items-center justify-center text-md leading-7 text-gray-50 bg-' . $theme . '-500 hover:bg-' . $theme . '-600 font-medium focus:ring-2 focus:ring-' . $theme . '-500 focus:ring-opacity-50 border border-transparent rounded-md shadow-sm ' . implode(' ', $extraClasses) . '" href="' . $link . '">' . $text . '</a>';
    }
    public static function infoBadge($text, $id, $theme): string
    {
        $html = '';
        $html .= '<i data-popover-target="' . $id . '" class="w-3 mx-1 h-6 text-gray-100 bg-' . $theme . '-500 text-center cursor-pointer ml-1 rounded-full border border-gray-300">i';
        $html .= '<div data-popover id="' . $id . '" role="tooltip" class="absolute z-10 invisible inline-block w-fit text-sm text-gray-900 transition-opacity duration-300 bg-white border border-gray-200 rounded-lg shadow-sm opacity-0 dark:text-gray-400 dark:border-gray-600 ' . BODY_DARK_COLOR_SCHEME_CLASS . '">';
        $html .= '<div class="px-3 py-2">';
        $html .= '<p>' . $text . '</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</i>';
        return $html;
    }
    public static function loadSpinner($id, $loadingText, $theme, $hidden = true): string
    {
        if ($hidden) {
            $hiddenClass = 'hidden';
        } else {
            $hiddenClass = '';
        }
        $html = '';
        $html .= '<div class="text-center ' . $hiddenClass . ' mt-6" id="' . $id . '">';
        $html .= '<div role="status">';
        $html .= '<svg aria-hidden="true" class="inline mr-2 w-8 h-8 text-gray-300 dark:text-white animate-spin fill-' . $theme . '-500 dark:fill-' . $theme . '-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                                            </svg>';
        $html .= '<span id="' . $id . '-span">' . $loadingText . '</span>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
    public static function submitButton($id, $text, $theme): string
    {
        return '<button id="' . $id . '" type="submit" class="mt-2 ml-2 my-2 text-white bg-' . $theme . '-500 hover:bg-' . $theme . '-600 focus:ring-4 focus:ring-' . $theme . '-300 font-medium rounded-lg text-sm p-2 me-2 mb-2 dark:bg-' . $theme . '-600 dark:hover:bg-' . $theme . '-700 focus:outline-none dark:focus:ring-' . $theme . '-800">' . $text . '</button>';
    }
    public static function submitButtonX($id, $title): string
    {
        return '<button id="' . $id . '" type="submit" class="cursor-pointer hover:text-red-700" title="' . $title . '">&#10060;</button>';
    }
    public static function simpleButton($id, $text, $theme): string
    {
        return '<button id="' . $id . '" type="button" class="w-16 h-10 bg-' . $theme . '-500 hover:bg-' . $theme . '-600 focus:ring-' . $theme . '-500 focus:ring-offset-' . $theme . '-200 text-white transition ease-in duration-200 text-center text-base font-semibold shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 rounded-lg">' . $text . '</button>';
    }
    public static function toggleCheckBox($id, $name, $text, $checked, $theme, $disabled = false): string
    {
        if ($checked) {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        if ($disabled) {
            $disabled = 'disabled';
        } else {
            $disabled = '';
        }
        $html = '';
        $html = '<div class="flex items-center justify-start">';
        $html .= '<label class="my-2 mx-2 relative inline-flex items-center cursor-pointer">';
        $html .= '<input type="checkbox" title="' . $name . ' placeholder="" id="' . $id . '" name="' . $name . '" class="sr-only peer awm-toggle" ' . $checked . ' ' . $disabled . ' />';
        $html .= '<div class="w-11 h-6 bg-gray-300 rounded-full peer peer-focus:ring-4 peer-focus:ring--300 dark:peer-focus:ring-' . $theme . '-800 dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-' . $theme . '-600"></div>';
        $html .= '</label>';
        $html .= '<span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">' . $text . '</span>';
        $html .= '</div>';
        return $html;
    }
    public static function searchInput($theme): string
    {
        $classes = [
            'filterSearch',
            'my-2',
            'py-2',
            'px-4',
            'w-48',
            'h-8',
            'text-sm',
            BODY_COLOR_SCHEME_CLASS,
            BODY_DARK_COLOR_SCHEME_CLASS,
            'outline-none',
            'appearance-none',
            'border',
            'border-gray-300',
            'rounded-lg',
            TEXT_COLOR_SCHEME,
            TEXT_DARK_COLOR_SCHEME,
            'leading-tight',
            'focus:outline-none',
            'focus:' . BODY_COLOR_SCHEME_CLASS,
            'focus:border-' . $theme . '-500',
            'dark:border-gray-600',
            'dark:placeholder-gray-400',
            'dark:focus:ring-' . $theme . '-500',
            'dark:focus:border-' . $theme . '-500'
        ];
        return '<input type="search" class="' . implode(' ', $classes) . '" placeholder="search..." />';
    }
    public static function popOver($text, $id, $theme = 'gray'): string
    {
        $html = '';
        $html .= '<button data-popover-target="' . $id . '" data-popover-placement="bottom-end" type="button"><svg class="w-4 h-4 ml-1 text-' . $theme . '-400 hover:text-' . $theme . '-500" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path></svg><span class="sr-only">Show information</span></button>';
        $html .= '<div data-popover id="' . $id . '" role="tooltip" class="max-w-fit absolute z-10 invisible inline-block text-sm text-gray-500 transition-opacity duration-300 ' . LIGHT_COLOR_SCHEME_CLASS . ' border border-gray-200 rounded-lg shadow-sm opacity-0 ' . BODY_DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-600 dark:text-gray-400">';
        $html .= '<div class="p-3 space-y-2">';
        $html .= $text;
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
    public static function backButton(string $theme): string
    {
        return '<button class="back-button mx-auto py-3 px-5 leading-5 text-white bg-' . $theme . '-500 hover:bg-' . $theme . '-600 font-medium text-center focus:ring-2 focus:ring-' . $theme . '-500 focus:ring-opacity-50 border border-transparent rounded-md shadow-sm">Go Back</button>';
    }
    public static function blockquote($text, $theme): string
    {
        return '<blockquote class="my-4 p-4 text-lg text-gray-900 bg-' . $theme . '-100 dark:bg-gray-800 dark:text-gray-300 border-l-4 border-' . $theme . '-500 rounded-lg">' . $text . '</blockquote>';
    }
    public static function ul(array $items): string
    {
        $html = '';
        $html .= '<ul class="list-disc list-inside">';
        foreach ($items as $item) {
            $html .= '<li class="my-2 text-sm text-gray-900 dark:text-gray-300">' . $item . '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
}
