<?php

declare(strict_types=1);

namespace App\Request;

class NativeHttp
{
    public static function get(string $url, array $headers = [], bool $sslIgnore = false, bool $expectJson = true): array
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

        $responseCode = isset($http_response_header[0])
            ? intval(self::getResponseCode($http_response_header[0]))
            : 0;

        if ($responseCode >= 400) {
            throw new \Exception($response, $responseCode);
        }

        if ($expectJson && $response !== false) {
            // If we expect JSON, decode it
            $decodedResponse = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("JSON decode error: " . json_last_error_msg(), 500);
            }
            return $decodedResponse;
        }
    }
    public static function post(string $url, array $data, bool $sendJson = false, array $headers = [], bool $sslIgnore = false): array
    {
        // Pack data
        if ($sendJson) {
            $data = json_encode($data);
        } else {
            $data = http_build_query($data);
        }
        // Options
        $options = [
            'http' => [
                'method' => 'POST',
                'ignore_errors' => true,
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

        $responseCode = intval(self::getResponseCode($http_response_header[0]));

        return json_decode($response, true);
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
}
