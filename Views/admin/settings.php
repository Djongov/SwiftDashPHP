<?php

declare(strict_types=1);

use App\Utilities\JsonSettingEditor;
use App\Security\Firewall;
use App\Api\Response;

// First firewall check
Firewall::activate();

// Admin check
if (!$isAdmin) {
    Response::output('You are not an admin', 403);
}

// Define WAF settings configuration
$wafSettingsPath = ROOT . '/config/settings.json';

// Render the WAF settings editor (now uses self-describing JSON structure)
echo JsonSettingEditor::renderJsonEditor($wafSettingsPath, $theme);
