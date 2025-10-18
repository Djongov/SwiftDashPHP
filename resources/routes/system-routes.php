<?php

declare(strict_types=1);

use FastRoute\RouteCollector;

function systemRoutes(RouteCollector $router, string $viewsFolder, string $controllersFolder, array $metadataArray)
{

    /* Views */

    // Login page
    $router->addRoute('GET', '/login', [$viewsFolder . '/landing/login.php', $metadataArray['main']]);
    // Install apge
    $router->addRoute('GET', '/install', [$viewsFolder . '/landing/install.php', $metadataArray['main']]);
    // Register page
    $router->addRoute('GET', '/register', [$viewsFolder . '/landing/register.php', $metadataArray['main']]);

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
    $router->addRoute('GET', '/adminx', [$viewsFolder . '/admin/index.php', $metadataArray['admin']]);
    $router->addRoute('GET', '/adminx/server', [$viewsFolder . '/admin/server.php', $metadataArray['admin']]);
    $router->addRoute('GET', '/adminx/csp-reports', [$viewsFolder . '/admin/csp/csp-reports.php', $metadataArray['admin']]);
    $router->addRoute('GET', '/adminx/csp-approved-domains', [$viewsFolder . '/admin/csp/csp-approved-domains.php', $metadataArray['admin']]);
    $router->addRoute(['GET', 'POST'], '/adminx/access-logs', [$viewsFolder . '/admin/access-logs.php', $metadataArray['admin']]);
    $router->addRoute('GET', '/adminx/firewall', [$viewsFolder . '/admin/firewall.php', $metadataArray['admin']]);
    $router->addRoute('GET', '/adminx/queries', [$viewsFolder . '/admin/queries.php', $metadataArray['admin']]);
    $router->addRoute('GET', '/adminx/mailer', [$viewsFolder . '/admin/mailer.php', $metadataArray['admin']]);
    $router->addRoute('GET', '/adminx/base64', [$viewsFolder . '/admin/tools/base64.php', $metadataArray['admin']]);
    $router->addRoute('GET', '/adminx/db-table', [$viewsFolder . '/admin/db-table.php', $metadataArray['admin']]);
    $router->addRoute('GET', '/adminx/apim', [$viewsFolder . '/admin/apim.php', $metadataArray['admin']]);
    $router->addRoute('GET', '/adminx/settings', [$viewsFolder . '/admin/settings.php', $metadataArray['admin']]);

    // Admin API
    $router->addRoute('POST', '/api/admin/csp/add', [$viewsFolder . '/api/admin/csp/add.php']);
    $router->addRoute('POST', '/api/admin/queries', [$viewsFolder . '/api/admin/queries.php']);
    $router->addRoute('POST', '/api/admin/api-keys', [$viewsFolder . '/api/admin/api-keys.php']);
    $router->addRoute('POST', '/api/admin/request-id', [$viewsFolder . '/api/admin/request-id.php']);

    // Tools API
    $router->addRoute('POST', '/api/tools/get-error-file', [$viewsFolder . '/api/tools/get-error-file.php']);
    $router->addRoute('POST', '/api/tools/clear-error-file', [$viewsFolder . '/api/tools/clear-error-file.php']);
    $router->addRoute('POST', '/api/tools/export-csv', [$viewsFolder . '/api/tools/export-csv.php']);
    $router->addRoute('POST', '/api/tools/export-tsv', [$viewsFolder . '/api/tools/export-tsv.php']);
    $router->addRoute('POST', '/api/tools/base64', [$viewsFolder . '/api/tools/base64.php']);
    $router->addRoute('POST', '/api/tools/php-info-parser', [$viewsFolder . '/api/tools/php-info-parser.php']);

    // JSON Settings API
    $router->addRoute('POST', '/api/edit-json-settings', [$viewsFolder . '/api/edit-json-settings.php']);

    /* API Routes */
    $router->addRoute(['GET','PUT','DELETE','POST'], '/api/user[/{id:[^/]+}]', [$viewsFolder . '/api/user.php']);
    $router->addRoute(['GET','PUT','DELETE','POST'], '/api/firewall[/{id:\d+}]', [$viewsFolder . '/api/firewall.php']);
    $router->addRoute('POST', '/api/mail/send', [$viewsFolder . '/api/mail/send.php']);
    $router->addRoute('POST', '/api/set-lang', [$viewsFolder . '/api/set-lang.php']);
    $router->addRoute('POST', '/api/cookie-consent', [$viewsFolder . '/api/consent-cookie.php']);

    /* DataGrid Api */
    $router->addRoute('POST', '/api/datagrid/get-records', [$viewsFolder . '/api/datagrid/get-records.php']);
    $router->addRoute('POST', '/api/datagrid/create-records', [$viewsFolder . '/api/datagrid/create-records.php']);
    $router->addRoute('POST', '/api/datagrid/update-records', [$viewsFolder . '/api/datagrid/update-records.php']);
    $router->addRoute('POST', '/api/datagrid/delete-records', [$viewsFolder . '/api/datagrid/delete-records.php']);
}
