<?php

declare(strict_types=1);

namespace App\Authentication\Azure;

use App\Request\HttpClient;
use App\Utilities\General;
use InvalidArgumentException;

/**
 * Shared pieces of the OAuth "authorization code" flow used to obtain Microsoft Graph access tokens
 * for an already logged-in Entra ID or MS Live user.
 *
 * The flow is: AccessToken::get() -> /auth/azure/request-access-token (begin) -> Microsoft
 * -> /auth/azure/{azure-ad,mslive}-code-exchange (consume + exchange) -> back to the original page.
 *
 * Microsoft is asked for response_mode=query, so the return trip is a top-level GET that carries the
 * session cookie in every environment (a cross-site form POST would not on plain http/localhost).
 * The state parameter is a random value bound to the session, so a code minted for somebody else
 * cannot be planted into a victim's session.
 */
class CodeExchange
{
    private const SESSION_KEY = 'access_token_request';

    /** How long a started request stays valid */
    private const TTL_SECONDS = 600;

    public const MSLIVE_AUTHORIZE_URL = 'https://login.microsoftonline.com/consumers/oauth2/v2.0/authorize?';
    public const MSLIVE_TOKEN_URL = 'https://login.microsoftonline.com/consumers/oauth2/v2.0/token';
    public const MSLIVE_SCOPE = 'https://graph.microsoft.com/user.read';

    /**
     * Start a request: remember where to send the user afterwards and return the state to send to Microsoft.
     */
    public static function begin(string $destination): string
    {
        $state = bin2hex(random_bytes(16));
        $_SESSION[self::SESSION_KEY] = [
            'state' => $state,
            'destination' => General::safeRedirectPath($destination),
            'created' => time(),
        ];
        return $state;
    }

    /**
     * Finish a request: validate the returned state against the session and return the destination.
     * Returns null when there is no pending request, the state does not match, or it is too old.
     * The pending request is always cleared, so a state can only be used once.
     */
    public static function consume(?string $state): ?string
    {
        $pending = $_SESSION[self::SESSION_KEY] ?? null;
        unset($_SESSION[self::SESSION_KEY]);

        if (!is_array($pending) || $state === null || !isset($pending['state'], $pending['created'])) {
            return null;
        }
        if (!hash_equals((string) $pending['state'], $state)) {
            return null;
        }
        if (time() - (int) $pending['created'] > self::TTL_SECONDS) {
            return null;
        }

        return General::safeRedirectPath($pending['destination'] ?? '/');
    }

    /**
     * Build the Microsoft authorize URL for a provider ('azure' or 'mslive').
     *
     * @param string $prompt 'none' for a silent attempt, 'consent' when the user must approve the scope
     */
    public static function authorizeUrl(string $provider, string $username, string $scope, string $state, string $prompt = 'none'): string
    {
        if ($provider === 'azure') {
            $url = ENTRA_ID_OAUTH_URL;
            $params = [
                'client_id' => ENTRA_ID_CLIENT_ID,
                'redirect_uri' => ENTRA_ID_CODE_REDIRECT_URI,
                'scope' => $scope,
            ];
        } elseif ($provider === 'mslive') {
            $url = self::MSLIVE_AUTHORIZE_URL;
            $params = [
                'client_id' => MS_LIVE_CLIENT_ID,
                'redirect_uri' => MS_LIVE_CODE_REDIRECT_URI,
                'scope' => self::MSLIVE_SCOPE,
            ];
        } else {
            throw new InvalidArgumentException('Unsupported provider: ' . $provider);
        }

        $params += [
            'response_type' => 'code',
            'response_mode' => 'query',
            'state' => $state,
            'prompt' => $prompt,
            'login_hint' => $username,
        ];

        return $url . http_build_query($params);
    }

    /**
     * Exchange an authorization code for a token response.
     *
     * @return array Token response from Microsoft, or ['error' => string, 'statusCode' => int] on failure
     */
    public static function exchange(string $provider, string $code): array
    {
        if ($provider === 'azure') {
            $tokenUrl = ENTRA_ID_TOKEN_URL;
            $postData = [
                'client_id' => ENTRA_ID_CLIENT_ID,
                'client_secret' => ENTRA_ID_CLIENT_SECRET,
                'redirect_uri' => ENTRA_ID_CODE_REDIRECT_URI,
            ];
        } elseif ($provider === 'mslive') {
            $tokenUrl = self::MSLIVE_TOKEN_URL;
            $postData = [
                'client_id' => MS_LIVE_CLIENT_ID,
                'client_secret' => MS_LIVE_CLIENT_SECRET,
                'redirect_uri' => MS_LIVE_CODE_REDIRECT_URI,
            ];
        } else {
            throw new InvalidArgumentException('Unsupported provider: ' . $provider);
        }

        $postData += [
            'grant_type' => 'authorization_code',
            'code' => $code,
        ];

        $client = new HttpClient($tokenUrl);
        $response = $client->call('POST', '', $postData, null, false, [], true);

        if (!is_array($response)) {
            return ['error' => 'Empty or invalid token response', 'statusCode' => 502];
        }

        // HttpClient returns ['error' => body, 'statusCode' => n] for HTTP errors; normalise the body to a message
        if (isset($response['error'], $response['statusCode'])) {
            $body = is_string($response['error']) ? json_decode($response['error'], true) : $response['error'];
            $message = is_array($body) ? (string) ($body['error_description'] ?? json_encode($body)) : (string) $response['error'];
            return ['error' => $message, 'statusCode' => (int) $response['statusCode']];
        }

        return $response;
    }
}
