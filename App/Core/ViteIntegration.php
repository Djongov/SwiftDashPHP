<?php

declare(strict_types=1);

namespace App\Core;

class ViteIntegration
{
    private static $manifest = null;
    private static $manifestPath = null;

    public static function init(): void
    {
        self::$manifestPath = ROOT . '/public/assets/react/manifest.json';
        
        if (file_exists(self::$manifestPath)) {
            $manifestContent = file_get_contents(self::$manifestPath);
            self::$manifest = json_decode($manifestContent, true);
        }
    }

    /**
     * Check if we're in development mode (Vite dev server running)
     */
    public static function isDev(): bool
    {
        // Check if Vite dev server is running on port 3000
        $devServerUrl = 'http://localhost:3000';
        $context = stream_context_create([
            'http' => [
                'timeout' => 1,
                'ignore_errors' => true
            ]
        ]);
        
        $result = @file_get_contents($devServerUrl, false, $context);
        return $result !== false;
    }

    /**
     * Get the URL for a Vite asset
     */
    public static function asset(string $path): string
    {
        if (self::isDev()) {
            return "http://localhost:3000/{$path}";
        }

        if (self::$manifest === null) {
            self::init();
        }

        if (self::$manifest && isset(self::$manifest[$path])) {
            return '/assets/react/' . self::$manifest[$path]['file'];
        }

        // Fallback to direct path
        return "/assets/react/{$path}";
    }

    /**
     * Get CSS files for a given entry point
     */
    public static function getCSS(string $entry = 'index.html'): array
    {
        if (self::isDev()) {
            return []; // CSS is injected by Vite dev server
        }

        if (self::$manifest === null) {
            self::init();
        }

        $cssFiles = [];
        
        if (self::$manifest && isset(self::$manifest[$entry])) {
            $entryData = self::$manifest[$entry];
            
            if (isset($entryData['css'])) {
                foreach ($entryData['css'] as $cssFile) {
                    $cssFiles[] = "/assets/react/{$cssFile}";
                }
            }
        }

        return $cssFiles;
    }

    /**
     * Get JS files for a given entry point
     */
    public static function getJS(string $entry = 'index.html'): array
    {
        if (self::isDev()) {
            return [
                'http://localhost:3000/@vite/client',
                "http://localhost:3000/{$entry}"
            ];
        }

        if (self::$manifest === null) {
            self::init();
        }

        $jsFiles = [];
        
        if (self::$manifest && isset(self::$manifest[$entry])) {
            $entryData = self::$manifest[$entry];
            $jsFiles[] = "/assets/react/" . $entryData['file'];

            // Add any imported chunks
            if (isset($entryData['imports'])) {
                foreach ($entryData['imports'] as $import) {
                    if (isset(self::$manifest[$import])) {
                        $jsFiles[] = "/assets/react/" . self::$manifest[$import]['file'];
                    }
                }
            }
        }

        return $jsFiles;
    }

    /**
     * Render React app scripts and styles
     */
    public static function renderReactApp(string $entry = 'src/main.tsx'): string
    {
        $html = '';
        
        // Add CSS files
        $cssFiles = self::getCSS($entry);
        foreach ($cssFiles as $css) {
            $html .= '<link rel="stylesheet" href="' . $css . '">' . PHP_EOL;
        }

        // Add JS files
        $jsFiles = self::getJS($entry);
        foreach ($jsFiles as $js) {
            if (self::isDev() && strpos($js, '@vite/client') !== false) {
                $html .= '<script type="module" src="' . $js . '"></script>' . PHP_EOL;
            } else {
                $html .= '<script type="module" src="' . $js . '"></script>' . PHP_EOL;
            }
        }

        return $html;
    }

    /**
     * Generate React app container
     */
    public static function renderReactContainer(): string
    {
        return '<div id="react-root"></div>';
    }

    /**
     * Check if current route should use React
     */
    public static function shouldUseReact(string $route): bool
    {
        // Define which routes should use React
        $reactRoutes = [
            '/react-dashboard',
            '/react-login',
            '/react-forms',
            // Add more routes as needed
        ];

        return in_array($route, $reactRoutes);
    }
}