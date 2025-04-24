<?php

declare(strict_types=1);

function dd()
{
    array_map(
        function ($x) {
        var_dump($x);
        }, func_get_args()
    );
    die;
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

function translate(string $key, array $replace = []): string
{
    static $translations = [];

    $lang = $_SESSION['lang'] ?? 'en'; // Default to English

    // Get project root (parent of public/)
    $projectRoot = ROOT;
    $file = "{$projectRoot}/config/lang/{$lang}.php";

    if (!isset($translations[$lang])) {
        if (file_exists($file)) {
            $translations[$lang] = include $file;
        } else {
            $translations[$lang] = []; // Prevent errors
        }
    }

    $text = $translations[$lang][$key] ?? $key;

    foreach ($replace as $search => $value) {
        $text = str_replace(":{$search}", $value, $text);
    }

    return $text;
}
