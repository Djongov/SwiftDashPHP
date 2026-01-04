<?php

declare(strict_types=1);

define(
    "ADMIN_MENU",
    [
    'Admin Home' => [
        'link' => '/adminx',
    ],
    'Settings' => [
        'loggedIn' => true,
        'icon' => [
            'type' => 'svg',
            'src' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.499-.183 1.06-.252 1.605-.217z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>'
        ],
        'link' => '/adminx/settings',
    ],
    'Server' => [
        'icon' => [
            'type' => 'svg',
            'src' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 17.25v-.228a4.5 4.5 0 00-.12-1.03l-2.268-9.64a3.375 3.375 0 00-3.285-2.602H7.923a3.375 3.375 0 00-3.285 2.602l-2.268 9.64a4.5 4.5 0 00-.12 1.03v.228m19.5 0a3 3 0 01-3 3H5.25a3 3 0 01-3-3m19.5 0a3 3 0 00-3-3H5.25a3 3 0 00-3 3m16.5 0h.008v.008h-.008v-.008zm-3 0h.008v.008h-.008v-.008z" />'
        ],
        'link' => '/adminx/server',
    ],
    'DB Table' => [
        'link' => '/adminx/db-table',
    ],
    'CSP' => [
        'link' => [
            'CSP Reports' => [
                'sub_link' => '/adminx/csp-reports'
            ],
            'CSP Approved Domains' => [
                'sub_link' => '/adminx/csp-approved-domains'
            ]
        ]
    ],
    'APIM' => [
        'link' => '/adminx/apim',
    ],
    'Firewall' => [
        'link' => '/adminx/firewall',
    ],
    'Queries' => [
        'link' => '/adminx/queries',
    ],
    'Migrate' => [
        'link' => '/adminx/migrate',
    ],
    'Access Logs' =>
    [
        'link' => '/adminx/access-logs'
    ],
    'Mailer' => (SENDGRID) ? [
        'icon' => [
            'type' => 'svg',
            'src' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />'
        ],
        'link' => '/adminx/sendgrid-mailer',
    ] : null,
    'Tools' => [
        'link' => [
            'Base64' => [
                'sub_link' => '/adminx/base64'
            ]
        ]
    ],
    ]
);
