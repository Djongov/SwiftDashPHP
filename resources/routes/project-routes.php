<?php

declare(strict_types=1);

use FastRoute\RouteCollector;
use App\Markdown\Page;

function projectRoutes(RouteCollector $router, string $viewsFolder, string $controllersFolder, array $metadataArray)
{

    /* Views */
    $router->addRoute('GET', '/', [$viewsFolder . '/landing/main.php', $metadataArray['main']]);
    $router->addRoute('GET', '/settings', [$viewsFolder . '/landing/settings.php', $metadataArray['main']]);

    // Example
    $router->addRoute('GET', '/charts', [$viewsFolder . '/example/charts.php', $metadataArray['main']]);
    $router->addRoute('GET', '/forms', [$viewsFolder . '/example/forms.php', $metadataArray['main']]);
    $router->addRoute('GET', '/datagrid', [$viewsFolder . '/example/datagrid.php', $metadataArray['main']]);
    $router->addRoute('GET', '/dummy', [$viewsFolder . '/example/dummy.php', $metadataArray['main']]);
    // Terms of service
    $router->addRoute('GET', '/terms-of-service', [$viewsFolder . '/landing/terms-of-service.php', $metadataArray['main']]);
    // Privacy policy
    $router->addRoute('GET', '/privacy-policy', [$viewsFolder . '/landing/privacy-policy.php', $metadataArray['main']]);
    // User settings page
    $router->addRoute('GET', '/user-settings', [$viewsFolder . '/landing/user-settings.php', $metadataArray['main']]);

    // Docs pages markdown auto routing for /docs
    $docsFolder = '/docs';
    $markDownFolder = $viewsFolder . $docsFolder;
    $router->addRoute('GET', '/docs', [$markDownFolder . '/index.php', Page::getMetaDataFromMd('index', $markDownFolder)]);
    // Search the /docs for files and build a route for each file
    $docFiles = Page::getMdFilesInDir($viewsFolder . '/docs');
    foreach ($docFiles as $file) {
        $router->addRoute('GET', '/docs/' . $file, [$markDownFolder . '/index.php', Page::getMetaDataFromMd($file, $markDownFolder)]);
    }

    // API Example
    $router->addRoute(['PUT', 'DELETE'], '/api/example/{id:\d+}', [$viewsFolder . '/api/example.php']);
    $router->addRoute('POST', '/api/example', [$viewsFolder . '/api/example.php']);
}
