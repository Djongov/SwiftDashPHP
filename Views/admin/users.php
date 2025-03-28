<?php

declare(strict_types=1);

use App\Api\Response;
use Components\DataGrid;
use App\Security\Firewall;
use Components\Alerts;

// First firewall check
Firewall::activate();

// Admin check
if (!$isAdmin) {
    Response::output('You are not an admin', 403);
}

echo DataGrid::fromDBTable('users', 'Users', $theme);
