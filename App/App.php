<?php

declare(strict_types=1);

namespace App;

use App\Page;
use App\Api\Response;

class App
{
    private array $skipRoutingUrls = [
        '/robots.txt',
        '/favicon.ico',
        '/health',
        '/ping',
        '/migrate'
    ];

    private array $skipBuildUrls = [
        '/migrate'
    ];

    public function init(): void
    {
        // Bootstrap: Load configuration and start session
        $this->bootstrap();

        // Get current URI
        $uri = $this->getCurrentUri();

        // Early exit for URLs that don't need routing
        if ($this->shouldSkipRouting($uri)) {
            $this->handleDirectUri($uri);
            return;
        }

        // Capture analytics (UTM parameters)
        $this->captureUtmParameters();

        // Handle routing
        $this->handleRouting($uri);
    }

    private function bootstrap(): void
    {
        // Load system settings first (required for AUTH_EXPIRY in Session)
        require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/functions.php';
        require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system-settings.php';
        require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/site-settings.php';

        // Start session (now AUTH_EXPIRY is available)
        \App\Core\Session::start();

        // Create a nonce for the session, that can be used for Azure AD authentication
        if (!isset($_SESSION['nonce'])) {
            $_SESSION['nonce'] = \App\Utilities\General::randomString(24);
        }

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
    }

    private function getCurrentUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        // Strip query string (?foo=bar) and decode URI
        if ($pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        return rawurldecode($uri);
    }

    private function shouldSkipRouting(string $uri): bool
    {
        foreach ($this->skipRoutingUrls as $skipUrl) {
            if ($uri === $skipUrl || str_starts_with($uri, $skipUrl)) {
                return true;
            }
        }
        return false;
    }

    private function shouldSkipBuild(string $uri): bool
    {
        foreach ($this->skipBuildUrls as $skipUrl) {
            if ($uri === $skipUrl || str_starts_with($uri, $skipUrl)) {
                return true;
            }
        }
        return false;
    }

    private function handleDirectUri(string $uri): void
    {
        // Handle URLs that bypass routing
        switch ($uri) {
            case '/health':
            case '/ping':
                header('Content-Type: application/json');
                echo json_encode(['status' => 'ok', 'timestamp' => time()]);
                break;
            case '/robots.txt':
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/robots.txt')) {
                    header('Content-Type: text/plain');
                    readfile($_SERVER['DOCUMENT_ROOT'] . '/robots.txt');
                }
                break;
            default:
                http_response_code(404);
                echo '404 Not Found';
        }
    }

    private function captureUtmParameters(): void
    {
        $utmSources = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];

        $currentUtms = [];
        foreach ($utmSources as $source) {
            if (isset($_GET[$source])) {
                $currentUtms[$source] = $_GET[$source];
            }
        }

        if (empty($currentUtms)) {
            return;
        }

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
            error_log("Failed to capture UTM parameters: " . $e->getMessage());
        }
    }

    private function handleRouting(string $uri): void
    {
        // Location of the routes definition
        $routesDefinition = require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/resources/routes.php';
        
        // Ensure that $routesDefinition is a callable
        if (!is_callable($routesDefinition)) {
            throw new \RuntimeException('Invalid routes definition');
        }
        
        $dispatcher = \FastRoute\simpleDispatcher($routesDefinition);

        // Fetch method and URI
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $isApi = str_contains($uri, '/api/');

        // Go through the login check
        $loginInfo = \App\RequireLogin::check($isApi);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                $this->handle404($httpMethod, $isApi, $loginInfo);
                break;
                
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $this->handle405($routeInfo[1]);
                break;
                
            case \FastRoute\Dispatcher::FOUND:
                $this->handleFoundRoute($routeInfo, $httpMethod, $uri, $isApi, $loginInfo);
                break;
        }
    }

    private function handle404(string $httpMethod, bool $isApi, array $loginInfo): void
    {
        if ($httpMethod === 'GET' && !$isApi) {
            $theme = $loginInfo['usernameArray']['theme'] ?? COLOR_SCHEME;
            $errorPage = new Page();
            echo $errorPage->build(
                '404 Not Found',
                'The page you are looking for was not found',
                ['404, Not Found'],
                OG_LOGO,
                $theme,
                MAIN_MENU,
                $loginInfo['usernameArray'],
                ROOT . '/Views/errors/error.php',
                $loginInfo['isAdmin'],
                []
            );
        } else {
            Response::output('api endpoint (' . $_SERVER['REQUEST_URI'] . ') not found', 404);
        }
    }

    private function handle405(array $allowedMethods): void
    {
        Response::output('Method not allowed. Allowed methods are: ' . implode(',', $allowedMethods), 405);
    }

    private function handleFoundRoute(array $routeInfo, string $httpMethod, string $uri, bool $isApi, array $loginInfo): void
    {
        $controllerInfo = $routeInfo[1];
        $controllerName = $controllerInfo[0];
        $params = $controllerInfo[1] ?? [];

        if (!file_exists($controllerName)) {
            throw new \Exception("Controller file ($controllerName) not found");
        }

        // Prepare common variables for controllers
        $usernameArray = $loginInfo['usernameArray'];
        $isAdmin = $loginInfo['isAdmin'];
        $theme = $loginInfo['usernameArray']['theme'] ?? COLOR_SCHEME;
        $loggedIn = $loginInfo['loggedIn'];

        // API Endpoints: Directly include and run
        if ($isApi) {
            include_once $controllerName;
            return;
        }

        // Non-API GET Endpoints with full page build
        if ($httpMethod === 'GET' && !empty($params) && !$this->shouldSkipBuild($uri)) {
            $menuArray = $params['menu'] ?? [];
            $page = new Page();
            echo $page->build(
                $params['title'],
                $params['description'],
                $params['keywords'],
                $params['thumbimage'],
                $theme,
                $menuArray,
                $usernameArray,
                $controllerName,
                $isAdmin,
                $routeInfo
            );
            return;
        }

        // Direct controller execution (skip Page::build wrapper)
        include_once $controllerName;
    }
}
