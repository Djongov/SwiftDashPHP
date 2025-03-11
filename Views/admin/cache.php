<?php declare(strict_types=1);

use App\Security\Firewall;
use App\Api\Response;
use Components\DataGrid;

// First firewall check
Firewall::activate();

// Admin check
if (!$isAdmin) {
    Response::output('You are not an admin', 403);
}

echo DataGrid::fromDBTable('cache', 'Cache', $theme);
