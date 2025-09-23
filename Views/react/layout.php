<?php

declare(strict_types=1);

use App\Core\ViteIntegration;
use Components\Page\Head;
use Components\Page\Menu;
use Components\Page\Footer;

// Initialize Vite integration
ViteIntegration::init();

$isDev = ViteIntegration::isDev();

?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <script nonce="1nL1n3JsRuN1192kwoko2k323WKE">
        (function() {
            const theme = localStorage.getItem('color-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.classList.add(theme);
        })();
    </script>
    
    <title><?= $title ?> - <?= SITE_TITLE ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $description ?>">
    <meta name="keywords" content="<?= implode(', ', $keywords) ?>">
    
    <!-- CSRF Token for React -->
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: <?= json_encode(THEME_COLORS) ?>
                    }
                }
            }
        }
    </script>
    
    <?php if ($isDev): ?>
        <!-- Vite Dev Server -->
        <script type="module" src="http://localhost:3000/@vite/client"></script>
    <?php endif; ?>
    
    <!-- React App CSS -->
    <?php
    $cssFiles = ViteIntegration::getCSS('src/main.tsx');
    foreach ($cssFiles as $css):
    ?>
        <link rel="stylesheet" href="<?= $css ?>">
    <?php endforeach; ?>
</head>

<body class="h-full bg-white dark:bg-gray-900">
    <!-- React App Container -->
    <div id="root"></div>

    <!-- React App JS -->
    <?php
    $jsFiles = ViteIntegration::getJS('src/main.tsx');
    foreach ($jsFiles as $js):
    ?>
        <script type="module" src="<?= $js ?>"></script>
    <?php endforeach; ?>

    <!-- Bootstrap Data for React -->
    <script>
        window.APP_DATA = {
            baseUrl: '<?= currentProtocolAndHost() ?>',
            csrfToken: '<?= $_SESSION['csrf_token'] ?? '' ?>',
            user: <?= json_encode($usernameArray ?? null) ?>,
            theme: '<?= $theme ?? COLOR_SCHEME ?>',
            isDev: <?= $isDev ? 'true' : 'false' ?>
        };
    </script>
</body>
</html>