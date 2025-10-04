<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Utilities\General;
use App\Api\Response;
use App\Core\Session;
use App\Authentication\AuthToken;
use Models\Core\DBCache;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * JWT (JSON Web Token) Handler
 * 
 * Provides secure JWT token generation, validation, and parsing functionality
 * with support for RS256 algorithm and proper claim validation.
 * 
 * @package App\Authentication
 * @author SwiftDashPHP Framework
 * @version 2.0
 */
class JWT
{
    /** @var string Default signing algorithm */
    private const DEFAULT_ALGORITHM = 'RS256';
    
    /** @var string Token type */
    private const TOKEN_TYPE = 'JWT';
    
    /** @var int Clock skew tolerance in seconds */
    private const CLOCK_SKEW_TOLERANCE = 60;
    
    /** @var array Required claims for local tokens */
    private const REQUIRED_CLAIMS = ['username', 'name', 'roles', 'last_ip'];
    
    /** @var array Supported signing algorithms */
    private const SUPPORTED_ALGORITHMS = ['RS256'];
    
    /** @var array Reserved JWT claims that cannot be overridden */
    private const RESERVED_CLAIMS = ['iss', 'exp', 'nbf', 'iat', 'jti'];
    /**
     * Generate a JWT token with the specified claims
     * 
     * @param array $claims User claims to include in the token
     * @param int $expiration Token expiration time in seconds (default from config)
     * @param string $algorithm Signing algorithm (default: RS256)
     * @return string The generated JWT token
     * @throws InvalidArgumentException If claims are invalid
     * @throws RuntimeException If token generation fails
     */
    public static function generateToken(
        array $claims, 
        int $expiration = JWT_TOKEN_EXPIRY,
        string $algorithm = self::DEFAULT_ALGORITHM
    ): string {
        self::validateClaims($claims);
        self::validateAlgorithm($algorithm);
        
        // Add standard JWT claims
        $currentTime = time();
        $claims = array_merge($claims, [
            'iss' => JWT_ISSUER,
            'exp' => $currentTime + $expiration,
            'nbf' => $currentTime - 1, // Not before (with 1 second tolerance)
            'iat' => $currentTime,     // Issued at
            'jti' => self::generateJti(), // JWT ID for uniqueness
        ]);

        $header = [
            'alg' => $algorithm,
            'typ' => self::TOKEN_TYPE,
        ];

        try {
            // Encode header and payload
            $base64UrlHeader = General::base64url_encode(json_encode($header, JSON_THROW_ON_ERROR));
            $base64UrlPayload = General::base64url_encode(json_encode($claims, JSON_THROW_ON_ERROR));
            
            // Create unsigned token
            $jwtUnsigned = $base64UrlHeader . '.' . $base64UrlPayload;
            
            // Sign the token
            $signature = self::signToken($jwtUnsigned, $algorithm);
            
            return $jwtUnsigned . '.' . $signature;
        } catch (Exception $e) {
            throw new RuntimeException('Failed to generate JWT token: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Validate user claims before token generation
     */
    private static function validateClaims(array $claims): void
    {
        // Check for reserved claims that shouldn't be manually set
        foreach (self::RESERVED_CLAIMS as $reservedClaim) {
            if (isset($claims[$reservedClaim])) {
                throw new InvalidArgumentException(
                    "Claim '{$reservedClaim}' is reserved and set automatically by the system"
                );
            }
        }
        
        // Check required claims for local tokens
        foreach (self::REQUIRED_CLAIMS as $requiredClaim) {
            if (!isset($claims[$requiredClaim])) {
                throw new InvalidArgumentException(
                    "Missing required claim: '{$requiredClaim}'. Required: " . implode(', ', self::REQUIRED_CLAIMS)
                );
            }
        }
        
        // Validate roles claim structure
        if (isset($claims['roles']) && !is_array($claims['roles'])) {
            throw new InvalidArgumentException('Roles claim must be an array');
        }
    }
    
    /**
     * Validate the signing algorithm
     */
    private static function validateAlgorithm(string $algorithm): void
    {
        if (!in_array($algorithm, self::SUPPORTED_ALGORITHMS, true)) {
            throw new InvalidArgumentException(
                "Unsupported algorithm: '{$algorithm}'. Supported: " . implode(', ', self::SUPPORTED_ALGORITHMS)
            );
        }
    }
    
    /**
     * Generate a unique JWT ID (jti)
     */
    private static function generateJti(): string
    {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * Sign the JWT token with the appropriate algorithm
     *
     * @param string $jwtUnsigned The unsigned JWT string (header.payload)
     * @param string $algorithm The signing algorithm to use
     * @return string The base64url encoded signature
     * @throws RuntimeException If signing fails
     * @throws InvalidArgumentException If algorithm is not supported
     */
    private static function signToken(string $jwtUnsigned, string $algorithm): string
    {
        $signature = '';
        
        if ($algorithm === 'RS256') {
            if (!openssl_sign($jwtUnsigned, $signature, base64_decode(JWT_PRIVATE_KEY), OPENSSL_ALGO_SHA256)) {
                throw new RuntimeException('Failed to sign JWT token with RS256');
            }
        } else {
            throw new InvalidArgumentException("Unsupported signing algorithm: {$algorithm}");
        }
        
        return General::base64url_encode($signature);
    }
    
    /**
     * Check if authentication token is set
     */
    public static function isTokenSet(): bool
    {
        return AuthToken::get() !== null;
    }
    /**
     * Parse a JWT token and return the header, payload, and signature
     *
     * @param string $token The JWT token to parse
     * @return array{0: array|null, 1: array|null, 2: string} [header, payload, signature]
     */
    public static function parse(string $token): array
    {
        $jwtParts = explode('.', $token);

        if (count($jwtParts) !== 3) {
            return [null, null, ''];
        }

        [$base64UrlHeader, $base64UrlPayload, $signature] = $jwtParts;

        // Decode the base64url-encoded header and payload
        $header = General::base64url_decode($base64UrlHeader);
        $payload = General::base64url_decode($base64UrlPayload);

        if (!$header || !$payload) {
            return [null, null, ''];
        }

        try {
            $decodedHeader = json_decode($header, true, 512, JSON_THROW_ON_ERROR);
            $decodedPayload = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
            
            return [$decodedHeader, $decodedPayload, $signature];
        } catch (Exception) {
            return [null, null, ''];
        }
    }
    /**
     * Validate a JWT token signature and structure
     *
     * @param string $token The JWT token to validate
     * @return bool True if token is valid, false otherwise
     */
    public static function validateToken(string $token): bool
    {
        [$header, $payload] = self::parse($token);
        
        if ($header === null || $payload === null) {
            return false;
        }
        
        // Validate header structure
        if (!isset($header['alg'], $header['typ']) ||
            $header['typ'] !== self::TOKEN_TYPE ||
            !in_array($header['alg'], self::SUPPORTED_ALGORITHMS, true)) {
            return false;
        }
        
        // Verify signature
        return self::verifySignature($token, $header['alg']);
    }
    
    /**
     * Verify the token signature
     */
    private static function verifySignature(string $token, string $algorithm): bool
    {
        $jwtParts = explode('.', $token);
        
        if (count($jwtParts) !== 3) {
            return false;
        }
        
        [$base64UrlHeader, $base64UrlPayload, $signature] = $jwtParts;
        $jwtUnsigned = $base64UrlHeader . '.' . $base64UrlPayload;
        $signatureToVerify = General::base64url_decode($signature);
        
        try {
            if ($algorithm === 'RS256') {
                $result = openssl_verify(
                    $jwtUnsigned,
                    $signatureToVerify,
                    base64_decode(JWT_PUBLIC_KEY),
                    OPENSSL_ALGO_SHA256
                );
                return $result === 1;
            }
            
            return false;
        } catch (Exception) {
            return false;
        }
    }
    /**
     * Parse and return only the payload of a JWT token
     *
     * @param string $token The JWT token
     * @return array The decoded payload or empty array if invalid
     */
    public static function parseTokenPayLoad(string $token): array
    {
        [, $payload] = self::parse($token);
        return $payload ?? [];
    }
    /**
     * Check if a token is expired or not yet valid
     *
     * @param string $token The JWT token to check
     * @return bool True if token is valid time-wise, false if expired or not yet valid
     */
    public static function checkExpiration(string $token): bool
    {
        $payload = self::parseTokenPayLoad($token);
        
        if (empty($payload) || !isset($payload['exp'])) {
            return false;
        }
        
        $currentTime = time();
        
        // Token is valid if not expired and (no nbf claim OR nbf time has passed)
        return $payload['exp'] >= $currentTime &&
               (!isset($payload['nbf']) || $payload['nbf'] <= $currentTime);
    }
    // A method to check the combined validity of a token
    public static function checkToken(string $token): bool
    {
        if (!self::validateToken($token)) {
            return self::handleValidationFailure();
        }

        if (!self::checkExpiration($token)) {
            return self::handleValidationFailure();
        }

        return true;
    }
    /**
     * Extract username from JWT token supporting multiple token types
     *
     * Supports local tokens (username), Azure AD (preferred_username), and Google (email)
     *
     * @param string $token The JWT token
     * @return string The extracted username or empty string if not found
     */
    public static function extractUserName(string $token): string
    {
        $payload = self::parseTokenPayLoad($token);
        
        // Priority order: username (local) -> preferred_username (Azure) -> email (Google)
        $usernameClaims = ['username', 'preferred_username', 'email'];
        
        foreach ($usernameClaims as $claim) {
            if (isset($payload[$claim]) && !empty($payload[$claim])) {
                return (string) $payload[$claim];
            }
        }
        
        return '';
    }
    /**
     * Handle token validation failure by cleaning up session and cache
     *
     * @return bool Always returns false to indicate validation failure
     */
    public static function handleValidationFailure(): bool
    {
        if (self::isTokenSet()) {
            // Clean up session and authentication state
            Session::reset();
            AuthToken::unset();
        }
        
        return false;
    }
}
