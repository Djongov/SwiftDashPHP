<?php

declare(strict_types=1);

use FastRoute\RouteCollector;

return function (RouteCollector $router) {
    // Common resources
    require_once ROOT . '/resources/menus/menus.php';

    // Include route groups
    require_once ROOT . '/resources/routes/system-routes.php';
    require_once ROOT . '/resources/routes/project-routes.php';


    $viewsFolder = ROOT . '/Views';
    $controllersFolder = ROOT . '/Controllers';

    $title = ucfirst(str_replace('-', ' ', basename($_SERVER['REQUEST_URI'])));

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
