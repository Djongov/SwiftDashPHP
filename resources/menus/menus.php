<?php

declare(strict_types=1);

/* Menu Settings */

define(
    "MAIN_MENU",
    [
    translate('docs_menu_name') => [
        'icon' => [
            'type' => 'svg',
            'src' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />'
        ],
        'link' => '/docs',
    ],
    'Charts' => [
        'link' => '/charts',
    ],
    'Forms' => [
        'link' => '/forms',
    ],
    'DataGrid' => [
        'link' => '/datagrid',
    ],
    '404' => [
        'link' => '/blablabla'
    ]
    ]
);

/* Username drop down menu */

define(
    "USERNAME_DROPDOWN_MENU",
    [
        translate('menu_user_settings') => [
            'path' => '/user-settings',
            'admin' => false
        ],
        translate('menu_admin') => [
            'path' => '/adminx',
            'admin' => true,
        ],
        translate('menu_logout') => [
            'path' => '/logout',
            'admin' => false,
        ]
    ]
);
