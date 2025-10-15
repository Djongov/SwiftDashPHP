<?php

declare(strict_types=1);

use App\Utilities\JsonSettingEditor;

// Define WAF settings configuration
$wafSettingsPath = ROOT . '/config/settings.json';

// Render the WAF settings editor (now uses self-describing JSON structure)
echo JsonSettingEditor::renderJsonEditor($wafSettingsPath, $theme);
