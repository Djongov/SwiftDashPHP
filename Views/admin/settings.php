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

// Define system settings configuration
$systemSettingsPath = ROOT . '/config/system-settings.json';

// Render the system settings editor (now uses self-describing JSON structure)
echo JsonSettingEditor::renderJsonEditor($systemSettingsPath, $theme);

// Define site settings configuration
$siteSettingsPath = ROOT . '/config/site-settings.json';

// Render the site settings editor (now uses self-describing JSON structure)
echo JsonSettingEditor::renderJsonEditor($siteSettingsPath, $theme);
