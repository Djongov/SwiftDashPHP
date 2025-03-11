<?php declare(strict_types=1);

namespace App\Logs;

use App\Authentication\JWT;
use App\Database\DB;
use App\Authentication\AuthToken;
use App\Utilities\General;

class SystemLog
{
    public static function write(string $message, string $category) : void
    {
        $username = AuthToken::get() != null ? JWT::extractUserName(AuthToken::get()) : 'unknown';
        $db = new DB();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("INSERT INTO system_log (text, client_ip, user_agent, created_by, category, uri, method) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute(
            [
                $message,
                currentIP(),
                General::currentBrowser() ?? '',
                $username,
                $category,
                General::fullUri(),
                $_SERVER['REQUEST_METHOD']
            ]
        );
    }
}
