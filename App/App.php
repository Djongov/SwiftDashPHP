<?php declare(strict_types=1);

namespace App;

use App\RequireLogin;
use App\Page;
use App\Core\Session;
use App\Api\Response;

class App
{
    public function init() : void
    {
        // Start session
        Session::start();

        // Create a nonce for the session, that can be used for Azure AD authentication. It's important this stays above calling the site-settings.php file, as it's used there
        if (!isset($_SESSION['nonce'])) {
            $_SESSION['nonce'] = \App\Utilities\General::randomString(24);
        }

        // Require the functions file
        require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/functions.php';

        // Now that we've loaded the env, let's get the site settings
        require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/site-settings.php';

        /*
            Now Routing
        */
        // Location of the routes definition
        $routesDefinition = require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/resources/routes.php';
        // Ensure that $routesDefinition is a callable
        if (!is_callable($routesDefinition)) {
            throw new \RuntimeException('Invalid routes definition');
        }
        $dispatcher = \FastRoute\simpleDispatcher($routesDefinition);

        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if ($pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                if ($httpMethod === 'GET' && !str_contains($uri, '/api/')) {
                    $loginInfoArray = RequireLogin::check(false);
                    // Theme
                    $theme = (isset($loginInfoArray['usernameArray']['theme'])) ? $loginInfoArray['usernameArray']['theme'] : COLOR_SCHEME;
                    $errorPage = new Page();
                    echo $errorPage->build(
                        '404 Not Found', // Title
                        'The page you are looking for was not found', // Description
                        ['404, Not Found'], // Keywords
                        OG_LOGO, // Thumbimage
                        $theme, // Theme
                        MAIN_MENU, // Menu
                        $loginInfoArray['usernameArray'], // Username array
                        dirname($_SERVER['DOCUMENT_ROOT']) . '/Views/errors/error.php', // Controller
                        $loginInfoArray['isAdmin'] // isAdmin
                    );
                } else {
                    // For non-GET requests, provide an API response
                    Response::output('api endpoint (' . $uri . ') not found', 404);
                }
                break;

            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // Handle 405 Method Not Allowed
                Response::output('Method not allowed. Allowed methods are: ' . implode(',', $allowedMethods), 405);
                break;

            case \FastRoute\Dispatcher::FOUND:
                $controllerName = $routeInfo[1][0]; // the controller file
                // This is how we can tell if it's an API endpoint
                $api = (str_contains($controllerName, '/api/')) ? true : false;
                // Now let's capture all the parameters $routeInfo[1][1]['metadata']
                $params = isset($routeInfo[1][1]['metadata']) ? $routeInfo[1][1]['metadata'] : [];
                // Include and execute the PHP file
                if (file_exists($controllerName)) {
                    $loginInfoArray = RequireLogin::check($api);
                    // Assign some variables to pass on to the view for general use
                    $usernameArray = $loginInfoArray['usernameArray'];
                    $vars['usernameArray'] = $usernameArray;
                    $loggedIn = $loginInfoArray['loggedIn'];
                    $vars['loggedIn'] = $loggedIn;
                    $isAdmin = $loginInfoArray['isAdmin'];
                    $vars['isAdmin'] = $isAdmin;
                    $theme = (isset($usernameArray['theme'])) ? $usernameArray['theme'] : COLOR_SCHEME;
                    $vars['theme'] = $theme;
                    /* Do login check */
                    if ($api) {
                        include_once $controllerName;
                    } else {
                        if ($httpMethod === 'GET') {
                            if (!empty($params)) {
                                $menuArray = $params['menu'];
                                $page = new Page();
                                echo $page->build($params['title'], $params['description'], $params['keywords'], $params['thumbimage'], $theme, $menuArray, $usernameArray, $controllerName, $isAdmin);
                            } else {
                                include_once $controllerName;
                            }
                        } else {
                            include_once $controllerName;
                        }
                    }
                } else {
                    throw new \Exception('Controller file (' . $controllerName . ') not found');
                }
                break;
        }
    }
}
