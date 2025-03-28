<?php

declare(strict_types=1);

namespace Components;

class Gallery
{
    public static function fromFolder($folder, $title, $caption = false, $links = false, $width = '100%', $height = 'auto'): string
    {

        $image_array = array_diff(scandir($folder), ['..', '.']);

        $image_array = array_map(fn ($item) => ($folder === '/') ? ($folder . $item) : ($folder . '/' . $item), $image_array);

        // Now strip the root folder from the image array
        $image_array = array_map(fn ($item) => str_replace($_SERVER['DOCUMENT_ROOT'], '', $item), $image_array);

        $html = '';

        $html .= '<figure class="my-4 flex flex-row flex-wrap justify-evenly items-center">';

        foreach ($image_array as $src) {
            if ($links) {
                $html .= '<a class="m-4" href="' . $src . '" target="_blank" title="' . $title . '"><img src="' . $src . '" title="' . $title . '" alt="' . $title . '" height="' . $height . '" width="' . $width . '" /></a>';
            } else {
                $html .= '<img class="m-4" src="' . $src . '" title="' . $title . '" alt="' . $title . '" height="' . $height . '" width="' . $width . '" />';
            }
        }

        if ($caption) {
            $html .= '<figcaption>' . $title . '</figcaption>';
        }

        $html .= '</figure>';

        return $html;
    }
}
