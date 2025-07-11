<?php

declare(strict_types=1);

use App\Markdown\Page;
use Components\DocsMenu;

$fileName = $_SERVER['REQUEST_URI'];
$basePath = '/docs';
$fileName = str_replace($basePath, '', $fileName);
if ($fileName === '') {
    $fileName = '/index';
}
// Now to build the menu we need to scan the current directory for files
$files = scandir(__DIR__);
$files = array_diff($files, ['.', '..', 'index.php']);
$files = array_map(
    function ($file) {
    return str_replace('.md', '', $file);
    }, $files
);

$variableMap = [
    '{Company Name}' => COMPANY_NAME,
    '{Site Title}' => SITE_TITLE,
    '{CURRENT_SCHEME_HOST}' => currentProtocolAndHost(),
];

echo '<div class="flex flex-col md:flex-row">';
    echo DocsMenu::render($basePath, $files);
    echo Page::render(__DIR__ . DIRECTORY_SEPARATOR . $fileName, $variableMap, $theme);
echo '</div>';
