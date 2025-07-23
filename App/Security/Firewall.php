<?php

declare(strict_types=1);

namespace App\Security;

use App\Database\DB;
use App\Api\Response;
use App\Logs\SystemLog;

class Firewall
{
    public static function cirdMatch($ip, $range)
    {
        // Allow all IPs by passing one of the below
        if ($range === '0.0.0.0' || $range === '0.0.0.0/32' || $range === '0.0.0.0/0') {
            return true;
        }

        list($subnet, $bits) = explode('/', $range);
        if ($bits === null) {
            $bits = 32;
        }
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
        return ($ip & $mask) == $subnet;
    }

    public static function activate()
    {
        // Find out the real client IP
        $clientIp = currentIP();
        $db = new DB();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM firewall");
        $stmt->execute();
        $firewallArray = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $allowList = [];
        foreach ($firewallArray as $array) {
            foreach ($array as $name => $value) {
                if ($name === 'ip_cidr') {
                    array_push($allowList, $value);
                }
            }
        }
        // Initiate validator variable
        $validIp = false;
        // Loop through the allow list
        foreach ($allowList as $addr) {
            // If there is a match
            if (self::cirdMatch($clientIp, $addr)) {
                // Set the validator to true
                $validIp = true;
                // and break the loop
                break;
            }
        }
        if (!$validIp) {
            SystemLog::write('just tried to access the web app and got Unauthorized', 'Access');
            Response::output('Unauthorized access for IP Address ' . $clientIp . ' on uri ' . $_SERVER['REQUEST_URI'], 401);
        }
    }
}
