<?php

declare(strict_types=1);

use FastRoute\RouteCollector;

return function (RouteCollector $router) {
    // Common resources
    require_once dirname(__DIR__) . '/resources/menus/menus.php';

    // Include route groups
    require_once dirname(__DIR__) . '/resources/routes/system-routes.php';
    require_once dirname(__DIR__) . '/resources/routes/project-routes.php';

    $viewsFolder = dirname(__DIR__) . '/Views';
    $controllersFolder = dirname(__DIR__) . '/Controllers';

    $title = ucfirst(str_replace('-', ' ', basename($_SERVER['REQUEST_URI'] ?? '')));

    // remove query string from title
    if (strpos($title, '?') !== false) {
        $title = substr($title, 0, strpos($title, '?'));
    }

    $genericMetaDataArray = [
        'title' => (!empty($title)) ? $title : translate('home'),
        'description' => GENERIC_DESCRIPTION,
        'keywords' => GENERIC_KEYWORDS,
        'thumbimage' => OG_LOGO,
        'menu' => MAIN_MENU
    ];

    $genericMetaAdminDataArray = [
        'title' => (!empty($title)) ? $title : translate('home'),
        'description' => GENERIC_DESCRIPTION,
        'keywords' => GENERIC_KEYWORDS,
        'thumbimage' => OG_LOGO,
        'menu' => ADMIN_MENU
    ];

    $metadataArray = [
        'main' => $genericMetaDataArray,
        'admin' => $genericMetaAdminDataArray
    ];

    // Call each route group initializer
    systemRoutes($router, $viewsFolder, $controllersFolder, $metadataArray);
    projectRoutes($router, $viewsFolder, $controllersFolder, $metadataArray);
};
