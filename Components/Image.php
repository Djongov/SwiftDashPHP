<?php declare(strict_types=1);

namespace Components;

class Image
{
    public static function display($file, $alt = '', $title = '', $width = '100%', $height = 'auto', $link = true, $caption = '') : string
    {
        $html = '<div class="relative inline-block m-4">'; // Adjust the margin-bottom as needed
        if ($link) {
            $html .= '<a href="' . $file . '" target="_blank" title="' . $title . '" class="block">'; // Set display to block for full width
            $html .= '<img src="' . $file . '" title="' . $title . '" alt="' . $alt . '" height="' . $height . '" width="' . $width . '" />';
            $html .= '</a>';
        } else {
            $html .= '<img src="' . $file . '" title="' . $title . '" alt="' . $alt . '" height="' . $height . '" width="' . $width . '" />';
        }
        if ($caption) {
            $html .= '<figcaption class="font-semibold relative bottom-0 left-0 w-full text-center">' . $caption . '</figcaption>';
        }
        $html .= '</div>'; // Close container
        return $html;
    }
}

