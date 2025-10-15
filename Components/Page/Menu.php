<?php

declare(strict_types=1);

namespace Components\Page;

use Components\ThemeSwitcher;
use Components\LanguageSwitcher;

class Menu
{
    public static function render(array $array, array $usernameArray, bool $isAdmin, string $theme): string
    {
        // Start the nav
        $html = '<nav class="px-2 ' . LIGHT_COLOR_SCHEME_CLASS . ' border-gray-200 dark:border-gray-700 ' . DARK_COLOR_SCHEME_CLASS . '">';
        // Holder div, used to have justify-between
        $html .= '<div class="flex flex-wrap justify-center">';
        // Logo + href to homepage
        $html .= '<a href="/" class="flex items-center">' . LOGO_SVG . '<p class="font-semibold ml-2">' . SITE_TITLE . '</p></a>';
        // Button for mobile menu sandwich
        $html .= '<button data-collapse-toggle="mobile-menu" type="button" class="inline-flex justify-center items-center ml-6 text-gray-800 rounded-lg md:hidden hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-' . $theme . '-300 dark:text-gray-400 dark:hover:text-white dark:focus:ring-gray-500" aria-controls="mobile-menu-2" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <svg class="w-8 h-8" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                </button>';
        // Menu itself
        $html .= '<div class="hidden w-full md:block md:w-auto" id="mobile-menu">';
        $html .= '<ul class="' . BODY_COLOR_SCHEME_CLASS . ' md:bg-transparent mx-auto flex md:flex-row flex-col flex-wrap justify-center items-center p-4 mt-4 rounded-lg border border-gray-100 md:space-x-8 md:mt-0 md:text-sm md:font-medium md:border-0 ' . BODY_DARK_COLOR_SCHEME_CLASS . ' md:' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700">';
        $uniqueIdCounter = 0;
        foreach ($array as $name => $value) {
            // Mechanism to skip entries that should be under login only
            if (isset($value['require_login']) && !isset($usernameArray['username'])) {
                continue;
            }
            // For keys with no value, skip them
            if ($value === null) {
                continue;
            }
            if (isset($value['loggedIn']) && $value['loggedIn'] === true && !isset($usernameArray['username'])) {
                continue;
            }
            // If the link is an array, it must be a dropdown
            if (is_array($value['link'])) {
                $html .= '<li class="min-w-fit mx-auto">';
                $html .= '<div class="flex flex-row items-center">';
                if (isset($value['icon'])) {
                    if ($value['icon']['type'] === 'image') {
                        $html .= '<img class="w-6 h-4" src="' . $value['icon']['src'] . '" alt="' . $name . '" />';
                    } else {
                        $html .= '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-2 w-6 h-6 stroke-' . $theme . '-500">' . $value['icon']['src'] . '</svg>';
                    }
                }
                $html .= '<button id="dropdownNavbarLink" data-dropdown-toggle="dropdownNavbar-' . $uniqueIdCounter . '" class="text-base font-normal ml-0 flex justify-between hover:bg-' . $theme . '-500 hover:boder hover:border-black hover:text-white ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . ' dark:hover:text-white dark:focus:text-white dark:border-gray-700 dark:hover:bg-gray-700 hover:rounded-md p-1">' . $name . '<svg class="ml-1 w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg></button>';
                $html .= '</div>';
                $html .= '<div id="dropdownNavbar-' . $uniqueIdCounter . '" class="z-10 w-44 font-normal ' . LIGHT_COLOR_SCHEME_CLASS . ' rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600 block hidden" data-popper-reference-hidden="" data-popper-escaped="" data-popper-placement="bottom" style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate3d(381px, 66px, 0px);">';
                $html .= '<ul class="py-1 text-sm ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . '" aria-labelledby="dropdownLargeButton">';
                foreach ($value['link'] as $subName => $subArray) {
                    $html .= '<li class="min-w-fit">';
                    $html .= '<div class="flex flex-row items-center">';
                    $html .= '<a href="' . $subArray['sub_link'] . '" class="w-full text-center py-2 hover:bg-gray-300 dark:hover:bg-gray-600 dark:hover:text-white">';
                    if (isset($subArray['icon'])) {
                        if ($subArray['icon']['type'] === 'image') {
                            $html .= '<img class="w-6 h-4" src="' . $subArray['icon']['src'] . '" alt="' . $subName . '" />';
                        } else {
                            $html .= '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-2 w-6 h-6 stroke-' . $theme . '-500">' . $subArray['icon']['src'] . '</svg>';
                        }
                    }
                    $html .= $subName . '</a>';
                    $html .= '</div>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
                $html .= '</div>';
                $html .= '</li>';
                // If the link is not an array it must be just a simple link
            } else {
                $html .= '<li class="min-w-fit mx-auto">';
                $html .= '<div class="flex flex-row items-center">';
                if (isset($value['icon'])) {
                    if ($value['icon']['type'] === 'image') {
                        $html .= '<img class="w-8 h-6" src="' . $value['icon']['src'] . '" alt="' . $name . '" />';
                    } else {
                        $html .= '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-2 w-6 h-6 stroke-' . $theme . '-500">' . $value['icon']['src'] . '</svg>';
                    }
                }
                $html .= '<a href="' . $value['link'] . '" class="min-w-fit block py-2 text-base font-normal hover:bg-' . $theme . '-500 hover:boder hover:border-black hover:text-white ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . ' dark:hover:text-white dark:focus:text-white dark:border-gray-700 dark:hover:bg-gray-700 hover:rounded-md p-1">' . $name . '</a>';
                $html .= '</div>';
                $html .= '</li>';
            }
            $uniqueIdCounter++;
        }
        $html .= '</ul>';
        $html .= self::switcherLanguageLogin($theme, $isAdmin, $usernameArray, true);
        $html .= '</div>';
        $html .= self::switcherLanguageLogin($theme, $isAdmin, $usernameArray, false);
        $html .= '</nav>';
        return $html;
    }
    public static function switcherLanguageLogin(string $theme, bool $isAdmin, null|array $usernameArray, bool $visibleOnMobile): string
    {
        // Set container visibility based on screen size
        $containerClasses = $visibleOnMobile
        ? 'flex items-center justify-center gap-2 md:hidden'        // mobile: center horizontally + vertically
        : 'hidden md:flex md:order-2 items-center justify-center gap-2'; // desktop: same

        $html = '<div class="' . $containerClasses . '">';

        $html .= ThemeSwitcher::render();

        if (MULTILINGUAL) {
            $html .= LanguageSwitcher::render($theme);
        }
        if (isset($usernameArray['username']) && $usernameArray['username'] !== null) {
            $html .= self::dropDownUserMenu($usernameArray['name'], $theme, $isAdmin, $usernameArray['picture']);
        } else {
            $html .= '<a class="inline-flex items-center justify-center px-2 py-2 h-10 w-18 md:w-18 my-2 ml-4 text-lg leading-7 text-' . $theme . '-50 bg-' . $theme . '-500 hover:bg-' . $theme . '-600 font-medium focus:ring-2 focus:ring-' . $theme . '-500 focus:ring-opacity-50 border border-transparent rounded-md shadow-sm" href="/login">'
                . translate('login_button_text')
                . '</a>';
        }

        $html .= '</div>';
        return $html;
    }
    public static function dropDownUserMenu(string $name, string $theme, bool $isAdmin, null|string $picture): string
    {
        $dropDownBarId = uniqid();
        $html = '<div class="flex md:order-2">';
            $html .= '<div class="flex items-center justify-between ml-2">';
                $html .= '<div class="flex items-center">';
                    // Now the profile picture
        if ($picture !== null && !empty($picture)) {
            $html .= '<img class="w-8 h-8 rounded-full" src="' . $picture . '" alt="' . $name . '" />';
        } else {
            $html .= '<svg class="ml-1 w-12 h-12 stroke-' . $theme . '-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>';
        }
                    // Button to open the dropdown
                    $html .= '<button id="' . uniqid() . '" data-dropdown-toggle="' . $dropDownBarId . '" class="ml-1 text-sm flex justify-between items-center p-1 w-full font-medium hover:bg-' . $theme . '-500 rounded hover:text-gray-100 truncate max-w-sm cursor-pointer">
                            ' . $name;
                        // The dropdown arrow
                        $html .= '<svg class="ml-1 w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>';
                    $html .= '</button>';
                $html .= '</div>';
            $html .= '</div>';
            // Dropdown menu itself
            $html .= '<div id="' . $dropDownBarId . '" class="z-10 w-44 font-normal ' . LIGHT_COLOR_SCHEME_CLASS . ' rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600 block hidden" data-popper-reference-hidden="" data-popper-escaped="" data-popper-placement="bottom" style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate3d(381px, 66px, 0px);">';
            // Open the <ul>
                $html .= '<ul class="py-1 text-sm ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . '" aria-labelledby="dropdownLargeButton">';
                // Display the admin menus to admin users
        foreach (USERNAME_DROPDOWN_MENU as $name => $array) {
            if ($array['admin'] && $isAdmin) {
                $html .= '<li>';
                $html .= '<a href="' . $array['path'] . '" class="block py-2 px-4 hover:bg-gray-300 dark:hover:bg-gray-600 dark:hover:text-white">' . $name . '</a>';
                $html .= '</li>';
            } elseif (!$array['admin']) {
                // Display non-admin menu items to all users
                $html .= '<li>';
                $html .= '<a href="' . $array['path'] . '" class="block py-2 px-4 hover:bg-gray-300 dark:hover:bg-gray-600 dark:hover:text-white">' . $name . '</a>';
                $html .= '</li>';
            }
        }
                $html .= '</ul>';
            $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
}
