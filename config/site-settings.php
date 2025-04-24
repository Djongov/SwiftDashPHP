<?php

declare(strict_types=1);

// Site title, Goes on footer and main menu header
define("SITE_TITLE", translate('site_title'));

/*

Branding & SEO Settings

*/
// Whether to show the loading screen on page load
define("SHOW_LOADING_SCREEN", true);

define('SYSTEM_USER_AGENT', SITE_TITLE . '/' . $version . ' (+https://' . $_SERVER['HTTP_HOST'] . ')');

// Key words for SEO
define(
    "GENERIC_KEYWORDS", [
    SITE_TITLE,
    ]
);
// Site description for SEO
define("GENERIC_DESCRIPTION", translate('site_title'));

// Logo that sits on the menu and the footer
define("LOGO_SVG", '<svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>');

define("LOGO", "/assets/images/logo.svg");

// Logo for the SEO OG tags
define("OG_LOGO", 'https://' . $_SERVER['HTTP_HOST'] . '/assets/images/logo.svg');

// MSFT Logo
define('MS_LOGO', '<svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 23 23"><path fill="#f3f3f3" d="M0 0h23v23H0z"/><path fill="#f35325" d="M1 1h10v10H1z"/><path fill="#81bc06" d="M12 1h10v10H12z"/><path fill="#05a6f0" d="M1 12h10v10H1z"/><path fill="#ffba08" d="M12 12h10v10H12z"/></svg>');

// Google Logo
define('GOOGLE_LOGO', '<svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/><path d="M1 1h22v22H1z" fill="none"/></svg>');

/* Color Scheme */
// Default theme for unathenticated users and first-time logins, possible values: 'amber', 'green', 'stone', 'rose', 'lime', 'teal', 'sky', 'purple', 'red', 'fuchsia', 'indigo'

// This is a default color scheme for small parts such as buttons and links
define(
    "THEME_COLORS",
    [
    'sky',
    'cyan',
    'emerald',
    'teal',
    'blue',
    'indigo',
    'violet',
    'purple',
    'fuchsia',
    'green',
    'pink',
    'red',
    'rose',
    'orange',
    'yellow',
    'amber',
    'lime',
    'gray',
    'slate',
    'stone',
    ]
);

define("COLOR_SCHEME", "amber");

if (!in_array(COLOR_SCHEME, THEME_COLORS)) {
    die('COLOR_SCHEME must be one of the following: ' . implode(', ', THEME_COLORS));
}

$defaultBodyColor = 'gray';

if (!in_array($defaultBodyColor, THEME_COLORS)) {
    die('defaultBodyColor must be one of the following: ' . implode(', ', THEME_COLORS));
}
// This is the text while in the light mode
define("TEXT_COLOR_SCHEME", "text-gray-900"); // text-gray-900 is nice
// This is the text while in the dark mode
define("TEXT_DARK_COLOR_SCHEME", "dark:text-gray-200"); // dark:text-gray-100 is nice
// This is the background color of elements while in the light mode
define("LIGHT_COLOR_SCHEME_CLASS", "bg-$defaultBodyColor-100"); // bg-purple-300 is nice
// This is the background color of elements while in the dark mode
define("DARK_COLOR_SCHEME_CLASS", "dark:bg-$defaultBodyColor-900"); // dark:bg-purple-900 is nice
// This is the background color for the body while in the light mode
define("BODY_COLOR_SCHEME_CLASS", "bg-$defaultBodyColor-200"); // bg-purple-200 is nice
// This is the background color for the body while in the dark mode
define("BODY_DARK_COLOR_SCHEME_CLASS", "dark:bg-$defaultBodyColor-800"); // dark:bg-purple-800 is nice

// Data grid color schemes
$defaultDataGridColor = 'gray';

if (!in_array($defaultDataGridColor, THEME_COLORS)) {
    die('defaultDataGridColor must be one of the following: ' . implode(', ', THEME_COLORS));
}
// This is the background color for the table body while in the light mode
define("DATAGRID_TBODY_COLOR_SCHEME", "bg-$defaultDataGridColor-100");
// This is the background color for the table body while in the dark mode
define("DATAGRID_TBODY_DARK_COLOR_SCHEME", "dark:bg-$defaultDataGridColor-700");
// This is the background color for the table head while in the light mode
define("DATAGRID_THEAD_COLOR_SCHEME", "bg-$defaultDataGridColor-300");
// This is the background color for the table head while in the dark mode
define("DATAGRID_THEAD_DARK_COLOR_SCHEME", "dark:bg-$defaultDataGridColor-900");
// This is the table text color while in the light mode
define("DATAGRID_TEXT_COLOR_SCHEME", "text-gray-900");
// This is the table text color while in the dark mode
define("DATAGRID_TEXT_DARK_COLOR_SCHEME", "dark:text-gray-100");
