<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap-cli.php';

echo "This is a CLI script example.\n";

echo "I will now retrieve the site settings:\n";

use Models\AppSettings;

$settings = new AppSettings();

$allSettings = $settings->getAll();

echo "Here are the site settings:\n";

print_r($allSettings);