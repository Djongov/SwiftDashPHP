<?php

declare(strict_types=1);

function getLanguageFlag($code)
{
    $flags = [
        'en' => '🇬🇧',  // UK flag directly as an emoji
        'bg' => '🇧🇬',  // Bulgaria flag directly as an emoji (corrected)
    ];

    return $flags[$code] ?? '';  // Returns the flag for the language code, or an empty string if not found
}
function dd()
{
    array_map(
        function ($x) {
        var_dump($x);
        }, func_get_args()
    );
    die;
}
function searchArrayForKey($array, $key) {
    // Iterate through each element in the array
    foreach ($array as $k => $v) {
        // If the current key matches the one we're looking for
        if ($k === $key) {
            // If the value is an array, return it (you can modify this based on your data)
            if (is_array($v)) {
                return $v; // Returning the array
            } else {
                return $v; // Return the value if it's not an array
            }
        }

        // If the value is an array, recursively search through it
        if (is_array($v)) {
            $result = searchArrayForKey($v, $key);
            if ($result !== null) {
                return $result;
            }
        }
    }

    // If the key was not found, return null
    return null;
}
function currentIP(): string
{
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $client_ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $client_ip = str_replace(strstr($_SERVER['HTTP_CLIENT_IP'], ':'), '', $_SERVER['HTTP_CLIENT_IP']);
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        // or just use the normal remote addr
        $client_ip = $_SERVER['REMOTE_ADDR'];
    }
    return $client_ip;
}

function randomString(int $length = 64)
{
    $length = ($length < 4) ? 4 : $length;
    return bin2hex(random_bytes(($length - ($length % 2)) / 2));
}
function currentBrowser()
{
    return $_SERVER['HTTP_USER_AGENT'] ?? null;
}
function currentUrl()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}
function getApiKeyFromHeaders()
{
    $headers = getallheaders();

    // Convert the headers array keys to lowercase for case-insensitive search
    $headers = array_change_key_case($headers, CASE_LOWER);

    // Check for the API key in lowercase
    $apiKeyHeader = strtolower(API_KEY_NAME);

    if (isset($headers[$apiKeyHeader])) {
        return $headers[$apiKeyHeader];
    } else {
        return null; // or handle missing API key appropriately
    }
}
function translate(string $key, array $replace = [], $lang = DEFAULT_LANG): string
{
    static $translations = [];

    // If language in Session is set, use it
    if (isset($_SESSION['lang']) && $_SESSION['lang'] !== DEFAULT_LANG) {
        $lang = $_SESSION['lang'];
    }

    // Get project root (parent of public/)
    $projectRoot = ROOT;
    $file = "{$projectRoot}/config/lang/{$lang}.php";
    $projectFile = "{$projectRoot}/config/lang/{$lang}-project.php";

    if (!isset($translations[$lang])) {
        $core = file_exists($file) ? include $file : [];
        $project = file_exists($projectFile) ? include $projectFile : [];

        // Merge, giving priority to project-specific translations
        $translations[$lang] = array_merge($core, $project);
    }

    $text = $translations[$lang][$key] ?? $key;

    foreach ($replace as $search => $value) {
        $text = str_replace(":{$search}", $value, $text);
    }

    return $text;
}
