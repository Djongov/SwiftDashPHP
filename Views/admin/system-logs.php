<?php declare(strict_types=1);

use App\Security\Firewall;
use Components\DataGrid;
use App\Api\Response;

// First firewall check
Firewall::activate();

// Admin check
if (!$isAdmin) {
    Response::output('You are not an admin', 403);
}

echo DataGrid::fromDBTable('system_log', 'System Logs', $theme);
