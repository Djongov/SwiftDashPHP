<?php

declare(strict_types=1);

namespace Components;

use Components\Html;

class DocsMenu
{
    public static function render($base, $tree): string
    {
        // First let's sort the $tree array so that if there is a index key, it is first
        $indexKey = array_search('index', $tree);
        if ($indexKey !== false) {
            unset($tree[$indexKey]);
            array_unshift($tree, 'index');
        }
        // Let's see if the current link is the same as the current page
        $currentLink = $_SERVER['REQUEST_URI'];
        $currentLink = str_replace($base, '', $currentLink);
        // Now let's build the menu
        $html = '<aside id="default-sidebar" class="min-w-fit md:mx-2 mx-auto my-4 z-40 w-64 h-fit" aria-label="Sidebar">';
            $html .= '<div class="h-full mx-2 px-3 py-4 overflow-y-auto ' . LIGHT_COLOR_SCHEME_CLASS . ' ' . DARK_COLOR_SCHEME_CLASS . ' ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . '">';
                $html .= Html::h2(ucfirst(str_replace('/', '', $base)), true);
                $html .= Html::horizontalLine();
                $html .= '<ul class="space-y-2 font-medium">';
        foreach ($tree as $link) {
            $title = str_replace('-', ' ', $link);
            if ($link === 'index') {
                $link = '';
                $title = 'Home';
            } else {
                $link = '/' . $link;
            }
            $html .= '<li>';
            if ($currentLink === $link) {
                        $html .= '<a href="' . $base . $link . '" class="flex items-center p-2 rounded-lg dark:text-white bg-gray-100 dark:bg-gray-700 group border border-gray-900 dark:border-gray-400">';
            } else {
                            $html .= '<a href="' . $base . $link . '" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">';
            }
                                // First letter uppercase
                                    $title = ucfirst($title);
                                    $html .= '<span class="ms-3">' . $title . '</span>';
                                    $html .= '</a>';
                                    $html .= '</li>';
        }
                $html .= '</ul>';
            $html .= '</div>';
        $html .= '</aside>';
        return $html;
    }
}
