<?php

declare(strict_types=1);

use FastRoute\RouteCollector;

function projectRoutes(RouteCollector $router, string $viewsFolder, string $controllersFolder, array $metadataArray) {

    /* Views */
    $router->addRoute('GET', '/', [$viewsFolder . '/landing/main.php', $metadataArray['main']]);
    #$router->addRoute('GET', '/adminx/products', [$viewsFolder . '/admin/products.php', $metadataArray['admin']]);
    #$router->addRoute('GET', '/adminx/product-prices', [$viewsFolder . '/admin/product-prices.php', $metadataArray['admin']]);
    #$router->addRoute('POST', '/api/fetch-product', [$viewsFolder . '/api/price-watcher/fetch-product.php']);
}
