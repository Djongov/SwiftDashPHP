<?php

declare(strict_types=1);

namespace App\Utilities;

class IP
{
    public static function currentIP(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',      // Cloudflare
            'HTTP_X_CLIENT_IP',           // Azure App Service / Front Door
            'HTTP_X_FORWARDED_FOR',       // Standard proxy header (may contain multiple IPs)
            'HTTP_X_REAL_IP',             // Nginx proxy
            'HTTP_X_FORWARDED',           // Older proxy header
            'HTTP_FORWARDED_FOR',         // RFC 7239 variant
            'HTTP_FORWARDED',             // RFC 7239
            'HTTP_CLIENT_IP',             // Some proxies
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                // X-Forwarded-For can contain a comma-separated list; take the first (client) IP
                $ip = trim(explode(',', $_SERVER[$header])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'];
    }
    public static function isPublicIp($ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            return false;
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }
        return true;
    }
    // This method will check if a string is a valid IP address
    public static function isValidIp($ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        return true;
    }
    // This method will check if a string is a private IP
    public static function isPrivateIp($ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            return false;
        }
        return true;
    }
    // This method will check if a string is ipv6
    public static function isIpv6($ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return false;
        }
        return true;
    }
    // This method will check if an ip is from the CGNAT range (RFC 6598)
    public static function isCgnatIp($ip)
    {
        // Define the CGNAT range in CIDR notation
        $cgnatRange = '100.64.0.0/10';

        // Convert the IP and range to long integers
        $ipLong = ip2long($ip);
        list($rangeIp, $subnet) = explode('/', $cgnatRange);
        $rangeIpLong = ip2long($rangeIp);

        // Calculate the mask
        $mask = -1 << (32 - $subnet);

        // Check if the IP is within the CGNAT range
        return (($ipLong & $mask) == ($rangeIpLong & $mask)) ? true : false;
    }
}
