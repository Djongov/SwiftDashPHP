<?php

declare(strict_types=1);

namespace App;

use App\Page;
use App\Api\Response;

class App
{
    public function init(): void
    {
        // Start session
        \App\Core\Session::start();

        // Create a nonce for the session, that can be used for Azure AD authentication. It's important this stays above calling the site-settings.php file, as it's used there
        if (!isset($_SESSION['nonce'])) {
            $_SESSION['nonce'] = \App\Utilities\General::randomString(24);
        }

        require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/functions.php';
        require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system-settings.php';
        require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/site-settings.php';

        // Insert required files
        foreach ($pathsToIncludeInAppApp as $path) {
            if (file_exists(ROOT . DIRECTORY_SEPARATOR . $path)) {
                require_once ROOT . DIRECTORY_SEPARATOR . $path;
            } else {
                die('Required file ' . $path . ' not found in App\App');
            }
        }

        // Set the default language in session
        if (MULTILINGUAL && !isset($_SESSION['lang'])) {
            $_SESSION['lang'] = DEFAULT_LANG;
        }

        $utmSources = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];

        $currentUtms = [];
        foreach ($utmSources as $source) {

            if (isset($_GET[$source])) {
                $currentUtms[$source] = $_GET[$source];
            }
        }

        if (!empty($currentUtms)) {
            // Capture UTM parameters
            $captureUtm = new \Models\UtmCapturer();
            $data = [
                'ip_address' => currentIP(),
                'utm_source' => $currentUtms['utm_source'] ?? null,
                'utm_medium' => $currentUtms['utm_medium'] ?? null,
                'utm_campaign' => $currentUtms['utm_campaign'] ?? null,
                'utm_term' => $currentUtms['utm_term'] ?? null,
                'utm_content' => $currentUtms['utm_content'] ?? null,
                'referrer_url' => $_SERVER['HTTP_REFERER'] ?? null,
                'landing_page' => isset($_SERVER['REQUEST_URI'])
                    ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
                    : null
            ];

            try {
                $captureUtm->create($data);
            } catch (\Exception $e) {
                // Log the error or handle it as needed
                //throw new \Exception("Failed to capture UTM parameters: " . $e->getMessage());
                error_log("Failed to capture UTM parameters: " . $e->getMessage());
            }
            
        }

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

        $isApi = str_contains($uri, '/api/') ?? false;

        // Go through the login check
        $loginInfo = \App\RequireLogin::check($isApi);

        extract($loginInfo);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            /* Handle 404 Not Found */
            case \FastRoute\Dispatcher::NOT_FOUND:
                if ($httpMethod === 'GET' && !$isApi) {
                    // Theme
                    $theme = (isset($loginInfo['usernameArray']['theme'])) ? $loginInfo['usernameArray']['theme'] : COLOR_SCHEME;
                    $errorPage = new Page();
                    echo $errorPage->build(
                        '404 Not Found', // Title
                        'The page you are looking for was not found', // Description
                        ['404, Not Found'], // Keywords
                        OG_LOGO, // Thumbimage
                        $theme, // Theme
                        MAIN_MENU, // Menu
                        $loginInfo['usernameArray'], // Username array
                        ROOT . '/Views/errors/error.php', // Controller
                        $loginInfo['isAdmin'], // isAdmin
                        $routeInfo
                    );
                } else {
                    // For non-GET requests, provide an API response
                    Response::output('api endpoint (' . $uri . ') not found', 404);
                }
                break;
            /* Handle 405 Method Not Allowed */
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // Handle 405 Method Not Allowed
                Response::output('Method not allowed. Allowed methods are: ' . implode(',', $allowedMethods), 405);
                break;
            /* Handle FOUND ROUTE OK */
            case \FastRoute\Dispatcher::FOUND:
                $controllerInfo = $routeInfo[1];
                $controllerName = $controllerInfo[0]; // Path to controller file
                // Extract route parameters if any
                $params = $controllerInfo[1] ?? [];

                if (!file_exists($controllerName)) {
                    throw new \Exception("Controller file ($controllerName) not found");
                }

                // Check login status
                // $loginInfo['usernameArray'] = $loginInfo['usernameArray'];
                // $loginInfo['isAdmin'] = $loginInfo['isAdmin'];
                // $loginInfo['loggedIn'] = $loginInfo['loggedIn'];

                // // Set theme (fallback to default if not set)
                // $loginInfo['theme'] = $loginInfo['usernameArray']['theme'] ?? COLOR_SCHEME;

                // API Endpoints: Directly include and run
                if ($isApi) {
                    // Add those variables so they are available for API calls too before the include
                    $usernameArray = $loginInfo['usernameArray'];
                    $isAdmin = $loginInfo['isAdmin'];
                    $theme = $loginInfo['usernameArray']['theme'] ?? COLOR_SCHEME;
                    $loggedIn = $loginInfo['loggedIn'];

                    include_once $controllerName;

                    break;
                }
                // Non-API Endpoints
                if ($httpMethod === 'GET' && !empty($params) && !$isApi) {
                    $menuArray = $params['menu'] ?? [];
                    $page = new Page();
                    echo $page->build(
                        $params['title'],
                        $params['description'],
                        $params['keywords'],
                        $params['thumbimage'],
                        $loginInfo['usernameArray']['theme'] ?? COLOR_SCHEME,
                        $menuArray,
                        $loginInfo['usernameArray'],
                        $controllerName,
                        $loginInfo['isAdmin'],
                        $routeInfo // Pass route info to the controllers that are GET and build a page
                    );
                } else {
                    $usernameArray = $loginInfo['usernameArray'];
                    $isAdmin = $loginInfo['isAdmin'];
                    $theme = $loginInfo['usernameArray']['theme'] ?? COLOR_SCHEME;
                    $loggedIn = $loginInfo['loggedIn'];
                    // Include the controller file
                    include_once $controllerName;
                }
                break;
        }
    }
}
