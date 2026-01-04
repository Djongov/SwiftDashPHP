<?php

declare(strict_types=1);

use Components\Forms;
use Components\Html;
use App\Security\Firewall;
use App\Api\Response;
use Components\DataGrid;

// First firewall check
Firewall::activate();

// Admin check
if (!$isAdmin) {
    Response::output('You are not an admin', 403);
}

echo Html::p('Here you can control the firewall. You can add IPs to the firewall, remove them, or view the current list of IPs in the firewall. To put controllers under the firewall, you need to call Firewall::activate(), preferrably at the start of the controller. If you want to disable the firewall, add an entry for <strong>0.0.0.0/32</strong>');
// Provide a form that will allow the user to add a new domain to the CSP approved domains list
$firewallAddForm = [
    'inputs' => [
        'input' => [
            [
                'type' => 'text',
                'name' => 'ip_cidr',
                'label' => 'IP (can be in CIDR notation)',
                'required' => true,
                'placeholder' => '1.1.1.1/32',
                'description' => 'The IP to add to the firewall. If you don\'t provide a mask, it will default to /32.'
            ],
            [
                'type' => 'text',
                'name' => 'comment',
                'required' => false,
                'label' => 'Comment',
                'placeholder' => 'VPN Range',
                'description' => 'A comment for this IP (optional)'
            ]
        ]
    ],
    'action' => '/api/firewall',
    'reloadOnSubmit' => true,
    'submitButton' => [
        'size' => 'medium',
        'text' => 'Add'
    ],
];

echo '<div class="p-4 m-4 max-w-md ' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700">';
    echo Html::h2('Add IP', true);
    echo Html::p('Add an IP to the firewall. Use only CIDR notations');
    echo Forms::render($firewallAddForm, $theme);
echo '</div>';

echo DataGrid::fromDBTable('firewall', 'Firewall', $theme);
