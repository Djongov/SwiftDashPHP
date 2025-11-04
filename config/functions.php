<?php

declare(strict_types=1);

function isXml(string|false|null $response): bool
{
    if (!$response) {
        return false; // Empty or false response is not XML
    }

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($response);
    libxml_clear_errors();

    return $xml !== false;
}

function xmlToJson(string $xml): ?string
{
    libxml_use_internal_errors(true);

    $xmlObject = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
    if ($xmlObject === false) {
        return null; // Failed to parse XML
    }

    $json = json_encode($xmlObject, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return $json;
}
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
        },
        func_get_args()
    );
    die;
}
function isAssoc(array $array): bool
{
    if ([] === $array) {
        return false; // empty array = not associative
    }

    return array_keys($array) !== range(0, count($array) - 1);
}

function searchArrayForKey($array, $key)
{
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
    $ipSources = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_AZURE_CLIENTIP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
    ];

    foreach ($ipSources as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];

            if ($key === 'HTTP_X_FORWARDED_FOR') {
                // Take the first IP from a comma-separated list
                $ip = trim(explode(',', $ip)[0]);
            }

            if ($key === 'HTTP_CLIENT_IP') {
                // Remove port if present
                if (!\App\Utilities\IP::isIpv6($ip)) {
                    $ip = strtok($ip, ':');
                }
            }

            // Validate format (optional, but safe)
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    // Fallback
    return $_SERVER['REMOTE_ADDR'];
}
function getUserCountry(): ?string
{
    // Get the country code from the browser's language settings
    $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (preg_match('/([a-z]{2})-([A-Z]{2})/', $lang, $matches)) {
        return strtolower($matches[1]); // Return the language code
    } else {
        return null; // No country code found
    }
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
function currentProtocolAndHost()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
}
function getApiKeyFromHeaders(): ?string
{
    $headers = getallheaders();
    $headers = array_change_key_case($headers, CASE_LOWER); // Normalize
    $headerKey = strtolower(API_KEY_HEADER_NAME); // Normalize expected name

    if (!isset($headers[$headerKey])) {
        return null;
    } else {
        return $headers[$headerKey];
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
function base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function base64url_decode(string $data): string
{
    $data .= str_repeat('=', (4 - strlen($data) % 4) % 4); // Add padding
    return base64_decode(strtr($data, '-_', '+/'));
}
function containsArray($piece, $array, $case_sensitive = false)
{
    foreach ($array as $element) {
        if ($element === null || $piece === null) {
            continue;
        }
        if ($case_sensitive) {
            if (stristr($piece, $element) !== false) {
                return true;
            }
        } else {
            if (strstr($piece, $element) !== false) {
                return true;
            }
        }
    }
    return false;
}
function containsArrayReturnElement($piece, $array, $case_sensitive = false)
{
    foreach ($array as $element) {
        if ($case_sensitive) {
            if (stristr($piece, $element) !== false) {
                return $element;
            }
        } else {
            if (strstr($piece, $element) !== false) {
                return $element;
            }
        }
    }
    return false;
}
function compareIsEqualArray(array $array1, array $array2): bool
{
    return array_diff($array1, $array2) == [] && array_diff($array2, $array1) == [];
}
function decodeUnicodeString($str): string
{
    return preg_replace_callback(
        '/\\\\u([0-9a-fA-F]{4})/',
        function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        },
        $str
    );
}