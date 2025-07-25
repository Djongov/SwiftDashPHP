<?php

declare(strict_types=1);

namespace App\Request;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use App\Api\Response;

class HttpClient
{
    public $client;

    public function __construct($url)
    {
        $this->client = new Client(
            [
            'base_uri' => $url,
            'timeout'  => 600,
            'http_errors' => false,
            'verify' => CURL_CERT,
            'debug' => false,
            'allow_redirects' => false
            ]
        );
    }

    public function call($method, $path, $data = [], ?string $bearerToken = null, $sendJson = false, $headers = [], $expectJson = true): mixed
    {
        $method = strtoupper($method);


        $headers['User-Agent'] = SYSTEM_USER_AGENT;

        if ($method === 'POST' && $sendJson) {
            $headers['Content-Type'] = 'application/json';
        }

        if ($bearerToken !== null) {
            $headers['Authorization'] = 'Bearer ' . $bearerToken;
        }

        if ($method === 'GET' && !empty($data)) {
            $path .= '?' . http_build_query($data);
        }

        if ($method === 'POST' && !$sendJson) {
            $options['form_params'] = $data;
        }

        if ($method === 'PUT' || $method === 'PATCH' && $sendJson) {
            $headers['Content-Type'] = 'application/json';
            $options['json'] = $data;
        }

        if ($method === 'PUT' || $method === 'DELETE' || ($method === 'POST' && $sendJson)) {
            // Encode the data as JSON
            $jsonPayload = json_encode($data);

            // Set the JSON payload as the request body
            $options['body'] = $jsonPayload;
            $headers['Content-Type'] = 'application/json';
        }

        // Add the headers to the options array
        $options['headers'] = $headers;

        // If path is null, then use the base_uri
        if (empty($path)) {
            $path = '';
        }



        try {
            $response = $this->client->request($method, $path, $options);
            // Let's extract the statusCode
            $statusCode = $response->getStatusCode();

            // Let's extract the reasonPhrase
            $reasonPhrase = $response->getReasonPhrase();
            // Let's extract the headers
            $headers = $response->getHeaders();
            // Let's extract the response
            $responseParsed = $response->getBody()->getContents();
            // Let's return the response
            if ($statusCode >= 400) {
                if (empty($responseParsed)) {
                    return ['error' => null, 'statusCode' => $statusCode];
                    //return ['response' => 'Empty response from ' . $path . ' (' .$statusCode . ':' . $reasonPhrase . ')', 'statusCode' => $statusCode];
                } else {
                    return ['error' => $responseParsed, 'statusCode' => $statusCode];
                }
            } else {
                if ($expectJson) {
                    return $responseParsed = json_decode($responseParsed, true);
                } else {
                    return $responseParsed;
                }
            }
        } catch (ConnectException $e) {
            // Handle the connection exception
            Response::output('HttpClient ConnectException: ' . $e->getHandlerContext()['error'], 500);
        } catch (\UnexpectedValueException $e) {
            // Handle UnexpectedValueException here
            Response::output('HttpClient UnexpectedValueException: ' . $e->getMessage(), 400);
        } catch (\Exception $e) {
            // Handle other exceptions
            Response::output('HttpClient Exceptiion: ' . $e->getMessage(), 400);
        }
    }
    // This method will fetch only the headers of the constructor url
    public function fetchHeaders(): array
    {
        try {
            $response = $this->client->request('HEAD');
            return $response->getHeaders();
        } catch (RequestException $e) {
            // Handle the RequestException
            Response::output('HttpClient RequestException: ' . $e->getMessage(), 400);
        } catch (ConnectException $e) {
            // Handle other exceptions
            Response::output('ConnectException Exceptiion: ' . $e->getMessage(), 400);
        } catch (\Exception $e) {
            // Handle other exceptions
            Response::output('Exceptiion: ' . $e->getMessage(), 400);
        }
    }
}
