<?php

declare(strict_types=1);

namespace App\Authentication\Azure;

use App\Authentication\JWT;
use App\Request\HttpClient;
use App\Utilities\General;
use Models\Core\DBCache;

/**
 * Verifies id tokens issued for personal Microsoft accounts (MS Live / "consumers").
 *
 * Both issuers publish RS256 signing keys with x5c certificates, so verification mirrors
 * AzureAD::validateJWTSignature. Certificates are cached per kid in the cache table for a day.
 */
class MsLive
{
    /** issuer => JWKS url */
    public const ISSUERS = [
        'https://login.live.com' => 'https://nexus.passport.com/public/partner/discovery/key',
        'https://login.microsoftonline.com/9188040d-6c67-4c5b-b112-36a304b66dad/v2.0' => 'https://login.microsoftonline.com/consumers/discovery/v2.0/keys',
    ];

    private const CACHE_TYPE = 'x5c-mslive';

    public static function isMsLiveIssuer(?string $issuer): bool
    {
        return $issuer !== null && array_key_exists($issuer, self::ISSUERS);
    }

    /**
     * Full check: structure, expiry, issuer, audience (our MS Live client id), optional nonce and signature.
     *
     * @param bool $checkNonce Compare the token nonce with the session nonce (use during the login POST)
     */
    public static function check(string $token, bool $checkNonce = false): bool
    {
        if (!JWT::checkExpiration($token)) {
            return false;
        }

        [$header, $payload] = JWT::parse($token);

        if ($header === null || $payload === null) {
            return false;
        }

        if (($header['alg'] ?? '') !== 'RS256' || empty($header['kid'])) {
            return false;
        }

        if (!self::isMsLiveIssuer($payload['iss'] ?? null)) {
            return false;
        }

        if (!defined('MS_LIVE_CLIENT_ID') || ($payload['aud'] ?? null) !== MS_LIVE_CLIENT_ID) {
            return false;
        }

        if ($checkNonce) {
            $nonce = $payload['nonce'] ?? null;
            if ($nonce === null || !isset($_SESSION['nonce']) || !hash_equals((string) $_SESSION['nonce'], (string) $nonce)) {
                return false;
            }
        }

        $x5c = self::loadCertificate($payload['iss'], (string) $header['kid']);

        if ($x5c === null) {
            return false;
        }

        return self::verifySignature($token, $x5c);
    }

    private static function verifySignature(string $token, string $x5c): bool
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

        $certText = '-----BEGIN CERTIFICATE-----' . "\n" . chunk_split($x5c, 64) . '-----END CERTIFICATE-----';
        // A bad certificate is a verification failure, not a PHP warning
        $certObject = @openssl_x509_read($certText);

        if ($certObject === false) {
            return false;
        }

        $publicKey = openssl_pkey_get_public($certObject);

        if ($publicKey === false) {
            return false;
        }

        $result = openssl_verify(
            $base64UrlHeader . '.' . $base64UrlPayload,
            General::base64url_decode($base64UrlSignature),
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        return $result === 1;
    }

    /**
     * Fetch (and cache for a day) the x5c certificate for a kid from the issuer's JWKS.
     */
    private static function loadCertificate(string $issuer, string $kid): ?string
    {
        $cached = DBCache::get(self::CACHE_TYPE, $kid);

        if ($cached) {
            if (strtotime($cached['expiration']) > time()) {
                return $cached['value'];
            }
            DBCache::delete(self::CACHE_TYPE, $kid);
        }

        $client = new HttpClient(self::ISSUERS[$issuer]);
        $jwks = $client->call('GET', '', [], null, false, ['Accept' => 'application/json']);

        foreach ($jwks['keys'] ?? [] as $key) {
            if (($key['kid'] ?? null) === $kid && !empty($key['x5c'][0])) {
                DBCache::create($key['x5c'][0], date('Y-m-d H:i:s', strtotime('+1 day')), self::CACHE_TYPE, $kid);
                return $key['x5c'][0];
            }
        }

        return null;
    }
}
