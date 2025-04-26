<?php

declare(strict_types=1);

namespace Components;

class LiveSearchInput
{
    public static function render(string $searchForTextInClass, string $filterParentElement, string $theme, string $placeholder = "Търси продукт") : string
    {
        $html = '';
        $html .= '<label for="live-search-input" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>';
        $html .= '<div class="m-4 relative w-full max-w-2xl">';
            $html .= '<div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">';
                $html .= '<svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="false" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>';
            $html .= '</div>';
            $html .= '<input data-searchForTextInClass="' . $searchForTextInClass . '" data-parent-element="' . $filterParentElement . '" type="search" id="live-search-input" class="live-search-input block w-full p-4 ps-10 text-sm text-gray-900 border border-' . $theme . '-300 rounded-lg bg-gray-50 focus:ring-' . $theme . '-600 focus:border- ' . $theme . '-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring- ' . $theme . '-500 dark:focus:border- ' . $theme . '-500 outline-none" placeholder="' . $placeholder . '" required />';
        //$html .= '<button type="submit" class="text-white absolute end-2.5 bottom-2.5 bg-' . $theme . '-500 hover:bg-' . $theme . '-700 focus:ring-4 focus:outline-none focus:ring- ' . $theme . '-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-' . $theme . '-600 dark:hover:bg-' . $theme . '-700 dark:focus:ring- ' . $theme . '-800">Търси</button>';
        $html .= '</div>';
        return $html;
    }
}