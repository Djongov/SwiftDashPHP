<?php

declare(strict_types=1);

use FastRoute\RouteCollector;
use App\Markdown\Page;

return function (RouteCollector $router) {
    $viewsFolder = ROOT . '/Views';
    $controllersFolder = ROOT . '/Controllers';
    // include the menu data
    require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/resources/menus/menus.php';
    $title = ucfirst(str_replace('-', ' ', basename($_SERVER['REQUEST_URI'])));

    $genericMetaDataArray = [
        'metadata' => [
            // For title we need to extract the page title from the request URI and capitalize the first letter
            'title' => (!empty($title)) ? $title : 'Home',
            'description' => GENERIC_DESCRIPTION,
            'keywords' => GENERIC_KEYWORDS,
            'thumbimage' => OG_LOGO,
            'menu' => MAIN_MENU,
        ]
    ];
    $genericMetaAdminDataArray = [
        'metadata' => [
            'title' => (!empty($title)) ? $title : 'Home',
            'description' => GENERIC_DESCRIPTION,
            'keywords' => GENERIC_KEYWORDS,
            'thumbimage' => OG_LOGO,
            'menu' => ADMIN_MENU,
        ]
    ];

    /* Views */

    // Landing
    // Root page
    $router->addRoute('GET', '/', [$viewsFolder . '/landing/main.php', $genericMetaDataArray]);
    // Login page
    $router->addRoute('GET', '/login', [$viewsFolder . '/landing/login.php', $genericMetaDataArray]);
    // Install apge
    $router->addRoute('GET', '/install', [$viewsFolder . '/landing/install.php', $genericMetaDataArray]);
    // Register page
    $router->addRoute('GET', '/register', [$viewsFolder . '/landing/register.php', $genericMetaDataArray]);
    // User settings page
    $router->addRoute('GET', '/user-settings', [$viewsFolder . '/landing/user-settings.php', $genericMetaDataArray]);
    // Terms of service
    $router->addRoute('GET', '/terms-of-service', [$viewsFolder . '/landing/terms-of-service.php', $genericMetaDataArray]);
    // Privacy policy
    $router->addRoute('GET', '/privacy-policy', [$viewsFolder . '/landing/privacy-policy.php', $genericMetaDataArray]);

    // Example
    $router->addRoute('GET', '/charts', [$viewsFolder . '/example/charts.php', $genericMetaDataArray]);
    $router->addRoute('GET', '/forms', [$viewsFolder . '/example/forms.php', $genericMetaDataArray]);
    $router->addRoute('GET', '/datagrid', [$viewsFolder . '/example/datagrid.php', $genericMetaDataArray]);
    $router->addRoute('GET', '/dummy', [$viewsFolder . '/example/dummy.php', $genericMetaDataArray]);

    // Auth
    $router->addRoute('POST', '/auth/local', [$viewsFolder . '/auth/local.php']);
    $router->addRoute('GET', '/auth/google', [$viewsFolder . '/auth/google.php']);
    $router->addRoute('POST', '/auth/azure-ad', [$viewsFolder . '/auth/azure-ad.php']);
    $router->addRoute(['POST', 'GET'], '/auth/azure-ad-access-token', [$viewsFolder . '/auth/azure-ad-access-token.php']);
    $router->addRoute('GET', '/logout', [$viewsFolder . '/auth/logout.php']);

    // Azure and MS Live auth
    $router->addRoute('GET', '/auth/azure/request-access-token', [$viewsFolder . '/auth/azure/request-access-token.php']);
    $router->addRoute('POST', '/auth/azure/azure-ad-code-exchange', [$viewsFolder . '/auth/azure/azure-ad-code-exchange.php']);
    $router->addRoute('POST', '/auth/azure/mslive-code-exchange', [$viewsFolder . '/auth/azure/mslive-code-exchange.php']);

    // CSP report endpoiont
    $router->addRoute('POST', '/api/csp-report', [$viewsFolder . '/api/csp-report.php']);

    // Admin
    $router->addRoute('GET', '/adminx', [$viewsFolder . '/admin/index.php', $genericMetaAdminDataArray]);
    $router->addRoute('GET', '/adminx/server', [$viewsFolder . '/admin/server.php', $genericMetaAdminDataArray]);
    $router->addRoute('GET', '/adminx/users', [$viewsFolder . '/admin/users.php', $genericMetaAdminDataArray]);
    $router->addRoute('GET', '/adminx/api-access-log', [$viewsFolder . '/admin/api-access-log.php', $genericMetaAdminDataArray]);
    $router->addRoute('GET', '/adminx/cache', [$viewsFolder . '/admin/cache.php', $genericMetaAdminDataArray]);
    $router->addRoute('GET', '/adminx/system-logs', [$viewsFolder . '/admin/system-logs.php', $genericMetaAdminDataArray]);
    $router->addRoute('GET', '/adminx/csp-reports', [$viewsFolder . '/admin/csp/csp-reports.php', $genericMetaAdminDataArray]);
    $router->addRoute('GET', '/adminx/csp-approved-domains', [$viewsFolder . '/admin/csp/csp-approved-domains.php', $genericMetaAdminDataArray]);
    $router->addRoute('GET', '/adminx/access-logs', [$viewsFolder . '/admin/access-logs.php', $genericMetaAdminDataArray]);
    $router->addRoute('GET', '/adminx/firewall', [$viewsFolder . '/admin/firewall.php', $genericMetaAdminDataArray]);
    $router->addRoute('GET', '/adminx/queries', [$viewsFolder . '/admin/queries.php', $genericMetaAdminDataArray]);
    $router->addRoute('GET', '/adminx/mailer', [$viewsFolder . '/admin/mailer.php', $genericMetaAdminDataArray]);
    $router->addRoute('GET', '/adminx/base64', [$viewsFolder . '/admin/tools/base64.php', $genericMetaAdminDataArray]);

    // Admin API
    $router->addRoute('POST', '/api/admin/csp/add', [$viewsFolder . '/api/admin/csp/add.php']);
    $router->addRoute('POST', '/api/admin/queries', [$viewsFolder . '/api/admin/queries.php']);

    // Tools API
    $router->addRoute('POST', '/api/tools/get-error-file', [$viewsFolder . '/api/tools/get-error-file.php']);
    $router->addRoute('POST', '/api/tools/clear-error-file', [$viewsFolder . '/api/tools/clear-error-file.php']);
    $router->addRoute('POST', '/api/tools/export-csv', [$viewsFolder . '/api/tools/export-csv.php']);
    $router->addRoute('POST', '/api/tools/export-tsv', [$viewsFolder . '/api/tools/export-tsv.php']);
    $router->addRoute('POST', '/api/tools/base64', [$viewsFolder . '/api/tools/base64.php']);
    $router->addRoute('POST', '/api/tools/php-info-parser', [$viewsFolder . '/api/tools/php-info-parser.php']);

    /* API Routes */
    $router->addRoute(['GET','PUT','DELETE','POST'], '/api/user[/{id:[^/]+}]', [$viewsFolder . '/api/user.php']);
    $router->addRoute(['GET','PUT','DELETE','POST'], '/api/firewall[/{id:\d+}]', [$viewsFolder . '/api/firewall.php']);
    $router->addRoute('POST', '/api/mail/send', [$viewsFolder . '/api/mail/send.php']);
    $router->addRoute('POST', '/api/set-lang', [$viewsFolder . '/api/set-lang.php']);
    $router->addRoute('POST', '/api/cookie-consent', [$viewsFolder . '/api/consent-cookie.php']);

    /* DataGrid Api */
    $router->addRoute('POST', '/api/datagrid/get-records', [$viewsFolder . '/api/datagrid/get-records.php']);
    $router->addRoute('POST', '/api/datagrid/update-records', [$viewsFolder . '/api/datagrid/update-records.php']);
    $router->addRoute('POST', '/api/datagrid/delete-records', [$viewsFolder . '/api/datagrid/delete-records.php']);

    // Docs pages markdown auto routing for /docs
    $docsFolder = '/docs';
    $markDownFolder = $viewsFolder . $docsFolder;
    $router->addRoute('GET', '/docs', [$markDownFolder . '/index.php', Page::getMetaDataFromMd('index', $markDownFolder)]);
    // Search the /docs for files and build a route for each file
    $docFiles = Page::getMdFilesInDir($viewsFolder . '/docs');
    foreach ($docFiles as $file) {
        $router->addRoute('GET', '/docs/' . $file, [$markDownFolder . '/index.php', Page::getMetaDataFromMd($file, $markDownFolder)]);
    }

    // Test
    $router->addRoute('GET', '/test', [$viewsFolder . '/test.php', $genericMetaDataArray]);
    // API Example
    $router->addRoute(['PUT', 'DELETE'], '/api/example/{id:\d+}', [$viewsFolder . '/api/example.php']);
    $router->addRoute('POST', '/api/example', [$viewsFolder . '/api/example.php']);
};
