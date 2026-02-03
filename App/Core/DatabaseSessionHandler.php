<?php

declare(strict_types=1);

namespace App\Core;

use App\Database\DB;
use SessionHandlerInterface;

/**
 * Database-backed session handler for distributed environments
 * Stores sessions in database to work across multiple app instances/replicas
 */
class DatabaseSessionHandler implements SessionHandlerInterface
{
    private \PDO $pdo;
    private int $maxLifetime;
    private string $tableName = 'sessions';
    
    public function __construct(?DB $db = null, string $tableName = 'sessions')
    {
        $this->tableName = $tableName;
        
        // Get PDO connection
        if ($db === null) {
            $db = new DB();
        }
        
        $this->pdo = $db->getConnection();
        
        // Use AUTH_EXPIRY if defined, otherwise fall back to ini setting or default
        $this->maxLifetime = defined('AUTH_EXPIRY') ? (int) \AUTH_EXPIRY : ((int) ini_get('session.gc_maxlifetime') ?: 1440);
    }
    
    /**
     * Initialize session
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }
    
    /**
     * Close session
     */
    public function close(): bool
    {
        return true;
    }
    
    /**
     * Read session data
     */
    public function read(string $id): string|false
    {
        try {
            $driver = DB_DRIVER;
            
            // Use appropriate syntax for each database driver
            if ($driver === 'pgsql' || $driver === 'mysql') {
                $sql = "SELECT data FROM {$this->tableName} 
                        WHERE id = :id 
                        AND (expires_at IS NULL OR expires_at > NOW())";
            } elseif ($driver === 'sqlsrv') {
                $sql = "SELECT data FROM {$this->tableName} 
                        WHERE id = :id 
                        AND (expires_at IS NULL OR expires_at > GETDATE())";
            } else { // sqlite
                $sql = "SELECT data FROM {$this->tableName} 
                        WHERE id = :id 
                        AND (expires_at IS NULL OR expires_at > datetime('now'))";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result && isset($result['data'])) {
                return $result['data'];
            }
            
            return '';
        } catch (\PDOException $e) {
            error_log("Session read error: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Write session data
     */
    public function write(string $id, string $data): bool
    {
        try {
            $driver = DB_DRIVER;
            
            // Use database NOW() functions for consistent timestamps
            if ($driver === 'mysql') {
                $sql = "INSERT INTO {$this->tableName} (id, data, expires_at, last_activity) 
                        VALUES (:id, :data, DATE_ADD(NOW(), INTERVAL :lifetime SECOND), NOW())
                        ON DUPLICATE KEY UPDATE 
                        data = VALUES(data), 
                        expires_at = DATE_ADD(NOW(), INTERVAL :lifetime2 SECOND), 
                        last_activity = NOW()";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'id' => $id,
                    'data' => $data,
                    'lifetime' => $this->maxLifetime,
                    'lifetime2' => $this->maxLifetime
                ]);
            } elseif ($driver === 'pgsql') {
                $sql = "INSERT INTO {$this->tableName} (id, data, expires_at, last_activity) 
                        VALUES (:id, :data, NOW() + INTERVAL ':lifetime seconds', NOW())
                        ON CONFLICT (id) DO UPDATE SET 
                        data = EXCLUDED.data, 
                        expires_at = NOW() + INTERVAL ':lifetime2 seconds', 
                        last_activity = NOW()";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'id' => $id,
                    'data' => $data,
                    'lifetime' => $this->maxLifetime,
                    'lifetime2' => $this->maxLifetime
                ]);
            } elseif ($driver === 'sqlsrv') {
                // SQL Server MERGE statement
                $sql = "MERGE INTO {$this->tableName} AS target
                        USING (SELECT :id AS id) AS source
                        ON target.id = source.id
                        WHEN MATCHED THEN
                            UPDATE SET data = :data, expires_at = DATEADD(SECOND, :lifetime, GETDATE()), last_activity = GETDATE()
                        WHEN NOT MATCHED THEN
                            INSERT (id, data, expires_at, last_activity) 
                            VALUES (:id2, :data2, DATEADD(SECOND, :lifetime2, GETDATE()), GETDATE());";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'id' => $id,
                    'data' => $data,
                    'lifetime' => $this->maxLifetime,
                    'id2' => $id,
                    'data2' => $data,
                    'lifetime2' => $this->maxLifetime
                ]);
            } else { // sqlite
                // SQLite REPLACE
                $sql = "REPLACE INTO {$this->tableName} (id, data, expires_at, last_activity) 
                        VALUES (:id, :data, datetime('now', '+' || :lifetime || ' seconds'), datetime('now'))";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'id' => $id,
                    'data' => $data,
                    'lifetime' => $this->maxLifetime
                ]);
            }
            
            return true;
        } catch (\PDOException $e) {
            error_log("Session write error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Destroy session
     */
    public function destroy(string $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->tableName} WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return true;
        } catch (\PDOException $e) {
            error_log("Session destroy error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Garbage collection - remove expired sessions
     */
    public function gc(int $max_lifetime): int|false
    {
        try {
            $driver = DB_DRIVER;
            
            // Use appropriate syntax for each database driver
            if ($driver === 'pgsql' || $driver === 'mysql') {
                $sql = "DELETE FROM {$this->tableName} WHERE expires_at < NOW()";
            } elseif ($driver === 'sqlsrv') {
                $sql = "DELETE FROM {$this->tableName} WHERE expires_at < GETDATE()";
            } else { // sqlite
                $sql = "DELETE FROM {$this->tableName} WHERE expires_at < datetime('now')";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log("Session GC error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Create the sessions table if it doesn't exist
     */
    public static function createTable(?DB $db = null): bool
    {
        try {
            if ($db === null) {
                $db = new DB();
            }
            
            $pdo = $db->getConnection();
            
            $driver = DB_DRIVER;
            
            // Check if table already exists
            $tableName = 'sessions';
            
            if ($driver === 'mysql') {
                $checkSql = "SHOW TABLES LIKE '$tableName'";
                $stmt = $pdo->query($checkSql);
                if ($stmt->fetch()) {
                    return true; // Table already exists
                }
                
                $sql = "CREATE TABLE IF NOT EXISTS $tableName (
                    id VARCHAR(255) NOT NULL PRIMARY KEY,
                    data TEXT NOT NULL,
                    expires_at DATETIME NULL,
                    last_activity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_expires (expires_at),
                    INDEX idx_last_activity (last_activity)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            } elseif ($driver === 'pgsql') {
                $checkSql = "SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_name = '$tableName'
                )";
                $stmt = $pdo->query($checkSql);
                if ($stmt->fetchColumn()) {
                    return true; // Table already exists
                }
                
                $sql = "CREATE TABLE IF NOT EXISTS $tableName (
                    id VARCHAR(255) NOT NULL PRIMARY KEY,
                    data TEXT NOT NULL,
                    expires_at TIMESTAMP NULL,
                    last_activity TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                );
                CREATE INDEX IF NOT EXISTS idx_expires ON $tableName (expires_at);
                CREATE INDEX IF NOT EXISTS idx_last_activity ON $tableName (last_activity);";
            } elseif ($driver === 'sqlsrv') {
                $checkSql = "SELECT OBJECT_ID('$tableName', 'U')";
                $stmt = $pdo->query($checkSql);
                if ($stmt->fetchColumn()) {
                    return true; // Table already exists
                }
                
                $sql = "IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = '$tableName')
                BEGIN
                    CREATE TABLE $tableName (
                        id NVARCHAR(255) NOT NULL PRIMARY KEY,
                        data NVARCHAR(MAX) NOT NULL,
                        expires_at DATETIME2 NULL,
                        last_activity DATETIME2 NOT NULL DEFAULT GETDATE()
                    );
                    CREATE INDEX idx_expires ON $tableName (expires_at);
                    CREATE INDEX idx_last_activity ON $tableName (last_activity);
                END";
            } else { // sqlite
                $checkSql = "SELECT name FROM sqlite_master WHERE type='table' AND name='$tableName'";
                $stmt = $pdo->query($checkSql);
                if ($stmt->fetch()) {
                    return true; // Table already exists
                }
                
                $sql = "CREATE TABLE IF NOT EXISTS $tableName (
                    id TEXT NOT NULL PRIMARY KEY,
                    data TEXT NOT NULL,
                    expires_at TEXT NULL,
                    last_activity TEXT NOT NULL DEFAULT (datetime('now'))
                );
                CREATE INDEX IF NOT EXISTS idx_expires ON $tableName (expires_at);
                CREATE INDEX IF NOT EXISTS idx_last_activity ON $tableName (last_activity);";
            }
            
            $pdo->exec($sql);
            return true;
        } catch (\PDOException $e) {
            error_log("Failed to create sessions table: " . $e->getMessage());
            return false;
        }
    }
}
