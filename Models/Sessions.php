<?php

declare(strict_types=1);

namespace Models;

use App\Database\DB;
use App\Logs\SystemLog;

class Sessions
{
    private string $_table = 'sessions';
    protected DB $_db;

    public function __construct()
    {
        $this->_db = new DB();
    }

    /**
     * Check if a session exists by ID
     * @param string $sessionId
     * @return bool
     */
    public function exists(string $sessionId): bool
    {
        $query = "SELECT id FROM {$this->_table} WHERE id = ?";
        $stmt = $this->_db->getConnection()->prepare($query);
        $stmt->execute([$sessionId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) !== false;
    }

    /**
     * Get all active sessions
     * @param string|null $orderBy column to order by
     * @param string|null $sort ASC or DESC
     * @return array
     */
    public function getAll(?string $orderBy = 'last_activity', ?string $sort = 'DESC'): array
    {
        $pdo = $this->_db->getConnection();
        $driver = DB_DRIVER;
        
        $orderClause = $orderBy ? " ORDER BY {$orderBy} {$sort}" : '';
        
        if ($driver === 'pgsql' || $driver === 'mysql') {
            $sql = "SELECT id, data, expires_at, last_activity 
                    FROM {$this->_table} 
                    WHERE expires_at > NOW()" . $orderClause;
        } elseif ($driver === 'sqlsrv') {
            $sql = "SELECT id, data, expires_at, last_activity 
                    FROM {$this->_table} 
                    WHERE expires_at > GETDATE()" . $orderClause;
        } else { // sqlite
            $sql = "SELECT id, data, expires_at, last_activity 
                    FROM {$this->_table} 
                    WHERE expires_at > datetime('now')" . $orderClause;
        }
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get a specific session by ID
     * @param string $sessionId
     * @return array|false
     */
    public function get(string $sessionId): array|false
    {
        $pdo = $this->_db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM {$this->_table} WHERE id = ?");
        $stmt->execute([$sessionId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Parse session data to extract user information
     * @param string $sessionData Serialized session data
     * @param bool $isCurrent Whether this is the current session (for debug logging)
     * @return string Username or 'Unknown'
     */
    public function parseUsername(string $sessionData, bool $isCurrent = false): string
    {
        if (empty($sessionData)) {
            return 'Unknown';
        }

        // Try to find username in the serialized data
        // PHP session format is: key|serialized_value
        if (preg_match('/username\|s:\d+:"([^"]+)"/', $sessionData, $matches)) {
            return htmlspecialchars($matches[1]);
        }
        
        if (preg_match('/email\|s:\d+:"([^"]+)"/', $sessionData, $matches)) {
            return htmlspecialchars($matches[1]);
        }
        
        if (preg_match('/name\|s:\d+:"([^"]+)"/', $sessionData, $matches)) {
            return htmlspecialchars($matches[1]);
        }

        // Fallback: Try PHP's session_decode
        $previousSession = $_SESSION ?? [];
        $_SESSION = [];
        
        if (@session_decode($sessionData)) {
            $username = $_SESSION['username'] 
                ?? $_SESSION['email'] 
                ?? $_SESSION['name']
                ?? $_SESSION['user']['username'] 
                ?? $_SESSION['user']['email']
                ?? 'Unknown';
            
            // Restore the original session
            $_SESSION = $previousSession;
            
            return htmlspecialchars($username);
        }
        
        // Restore the original session
        $_SESSION = $previousSession;
        
        return 'Unknown';
    }

    /**
     * Delete a session (revoke)
     * @param string $sessionId
     * @param string $revokedBy Who is revoking the session
     * @return bool
     */
    public function delete(string $sessionId, string $revokedBy): bool
    {
        if (!$this->exists($sessionId)) {
            return false;
        }

        $pdo = $this->_db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM {$this->_table} WHERE id = ?");
        $stmt->execute([$sessionId]);
        
        if ($stmt->rowCount() > 0) {
            SystemLog::write("Session {$sessionId} revoked by {$revokedBy}", 'Sessions');
            return true;
        }
        
        return false;
    }

    /**
     * Delete all sessions except the specified one
     * @param string $currentSessionId The session to keep
     * @param string $clearedBy Who is clearing the sessions
     * @return int Number of sessions deleted
     */
    public function deleteAllExcept(string $currentSessionId, string $clearedBy): int
    {
        $pdo = $this->_db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM {$this->_table} WHERE id != ?");
        $stmt->execute([$currentSessionId]);
        
        $deletedCount = $stmt->rowCount();
        
        if ($deletedCount > 0) {
            SystemLog::write("Cleared {$deletedCount} sessions by {$clearedBy} (kept session {$currentSessionId})", 'Sessions');
        }
        
        return $deletedCount;
    }

    /**
     * Get count of active sessions
     * @return int
     */
    public function countActive(): int
    {
        $pdo = $this->_db->getConnection();
        $driver = DB_DRIVER;
        
        if ($driver === 'pgsql' || $driver === 'mysql') {
            $sql = "SELECT COUNT(*) as count FROM {$this->_table} WHERE expires_at > NOW()";
        } elseif ($driver === 'sqlsrv') {
            $sql = "SELECT COUNT(*) as count FROM {$this->_table} WHERE expires_at > GETDATE()";
        } else { // sqlite
            $sql = "SELECT COUNT(*) as count FROM {$this->_table} WHERE expires_at > datetime('now')";
        }
        
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return (int) ($result['count'] ?? 0);
    }
}
