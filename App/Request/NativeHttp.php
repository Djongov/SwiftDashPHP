<?php

declare(strict_types=1);

namespace App\Request;

class NativeHttp
{
    public static function get(string $url, array $headers = [], bool $sslIgnore = false, bool $expectJson = true): array|string
    {
        // Options
        $contextOptions = [
            'http' => [
                'method' => 'GET',
                'ignore_errors' => true,
            ],
            'ssl' => [
                'cafile' => CURL_CERT,
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ]
        ];

        if (parse_url($url, PHP_URL_HOST) === 'techmart.bg') {
            $sslIgnore = true; // Techmart requires SSL ignore due to certificate issues
        }

        if ($sslIgnore) {
            // Bypass SSL verification (not recommended)
            $contextOptions['ssl']['verify_peer'] = false;
            $contextOptions['ssl']['verify_peer_name'] = false;
        }

        if (!empty($headers)) {
            $formattedHeaders = [];

            foreach ($headers as $key => $value) {
                // If the header is already a full string like "Accept: application/json", keep it as-is
                if (is_int($key)) {
                    $formattedHeaders[] = $value;
                } else {
                    $formattedHeaders[] = "{$key}: {$value}";
                }
            }

            $contextOptions['http']['header'] = implode("\r\n", $formattedHeaders);
        }

        $context = stream_context_create($contextOptions);

        $response = file_get_contents($url, false, $context);

        // Try decoding only if it's actually gzipped
        $isGzipped = isset($http_response_header) && array_reduce($http_response_header, fn($carry, $line) => $carry || stripos($line, 'Content-Encoding: gzip') !== false, false);
        
        $decoded = $isGzipped ? gzdecode($response) : $response;

        if (isXml($decoded) && $expectJson) {
            $data = xmlToJson($decoded);
            if ($data === null) {
                dd([
                    'XML parse error' => libxml_get_errors(),
                    'XML content' => $decoded,
                    'URL' => $url,
                    'Headers' => $http_response_header
                ]);
            }
        } elseif ($expectJson && !isXml($decoded)) {
            $data = json_decode($decoded, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // echo '<pre>';
                // print_r([
                //     'JSON decode error' => json_last_error_msg(),
                //     'JSON content' => $decoded,
                //     'URL' => $url,
                //     'Headers' => $http_response_header
                // ]);
                // echo '</pre>';
                // die;
            }
        }

        $responseCode = isset($http_response_header[0])
            ? intval(self::getResponseCode($http_response_header[0]))
            : 0;

        if ($responseCode >= 400) {
            dd([
                'HTTP error' => $responseCode,
                'Response content' => $decoded,
                'URL' => $url,
                'Headers' => $http_response_header
            ]);
            throw new \Exception($data, $responseCode);
        }
        
        if (!isset($data) || !is_array($data)) {
            $data = $decoded; // Fallback to raw response if not JSON or XML
        }

        if (is_string($data) && str_starts_with($data, '{')) {
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON decode error: ' . json_last_error_msg(), 500);
            }
        }

        return $data;
    }
    public static function post(string $url, array|string $data, bool $sendJson = false, array $headers = [], bool $sslIgnore = false): array
    {
        // Pack data
        // if ($sendJson) {
        //     $data = json_encode($data);
        // } else {
        //     $data = http_build_query($data);
        // }
        // Options
        $options = [
            'http' => [
                'method' => 'POST',
                'ignore_errors' => false,
                'content' => $data
            ]
        ];
        if (!$sslIgnore) {
            self::sslOptions($url);
        }

        if (!empty($headers)) {
            $options['http']['header'] = $headers;
        }
        if ($sendJson) {
            $options['http']['header'][] = 'Content-Type: application/json';
        } else {
            $options['http']['header'][] = 'Content-Type: application/x-www-form-urlencoded';
        }

        $context  = stream_context_create($options);

        $response = file_get_contents($url, false, $context);

        // Decompress gzipped response if needed
        $isGzipped = isset($http_response_header) && array_reduce($http_response_header, fn($carry, $line) => $carry || stripos($line, 'Content-Encoding: gzip') !== false, false);
        
        $decoded = $isGzipped ? gzdecode($response) : $response;

        if ($decoded === false) {
            $decoded = $response; // Fallback to original response if decompression fails
        }

        if (isXml($decoded)) {
            $decoded = xmlToJson($decoded);
        }

        $decoded = json_decode($decoded, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            dd([
                'JSON decode error' => json_last_error_msg(),
                'JSON content' => $decoded,
                'URL' => $url,
                'Headers' => $http_response_header
            ]);
        }
        
        $responseCode = intval(self::getResponseCode($http_response_header[0]));

        return $decoded;
    }

    private static function getResponseCode($responseHeader)
    {
        if ($responseHeader != null) {
            preg_match('/\d{3}/', $responseHeader, $matches);
            return $matches[0] ?? null;
        }
    }
    public static function sslOptions($url)
    {
        return [
            'ssl' => [
                'cafile'            => CURL_CERT,
                //'peer_fingerprint'  => openssl_x509_fingerprint(file_get_contents('/path/to/key.crt')),
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => false,
                'verify_depth'      => 0,
                'CN_match'          => parse_url($url)['host']
            ]
        ];
    }
    public function diagnosticsCall(string $url) : array
    {
        // We want to return body, headers and response code in an array
        $responseArray = [
            'body' => '',
            'headers' => [],
            'response_code' => 0
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'ignore_errors' => true,
            ],
            'ssl' => $this->sslOptions($url)
        ]);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            $responseArray['body'] = 'Error fetching URL';
            return $responseArray;
        }

        $responseArray['body'] = $response;

        if (isset($http_response_header)) {
            $responseArray['headers'] = $http_response_header;
            $responseArray['response_code'] = self::getResponseCode($http_response_header[0]);
        } else {
            $responseArray['headers'] = [];
            $responseArray['response_code'] = 0;
        }

        return $responseArray;
    }
}
