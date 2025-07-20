<?php

declare(strict_types=1);

namespace Models;

use App\Database\DB;

class APIKeys extends BasicModel
{
    protected DB $db;
    protected string $table = 'api_keys';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = new DB();
    }
    public function get(string $apiKey): array
    {
        $pdo = $this->db->getConnection();
        $query = "SELECT * FROM {$this->table} WHERE api_key = :apiKey";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':apiKey', $apiKey);
        $stmt->execute();
        try {
            $stmt->execute();
            $array = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $array ?: [];
        } catch (\PDOException $e) {
            // Handle exception
            if (ERROR_VERBOSE) {
                throw new \Exception('Database error: ' . $e->getMessage(), 500);
            } else {
                throw new \Exception('Database error', 500);
            }
        }
    }
    public function getApiKeyByNote(string $note): ?string
    {
        $pdo = $this->db->getConnection();
        $query = "SELECT api_key FROM {$this->table} WHERE note = :note";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':note', $note);
        try {
            $stmt->execute();
            $result = $stmt->fetchColumn();
            return $result ?: null; // Return null if no API key found
        } catch (\PDOException $e) {
            // Handle exception
            if (ERROR_VERBOSE) {
                throw new \Exception('Database error: ' . $e->getMessage(), 500);
            } else {
                throw new \Exception('Database error', 500);
            }
        }
    }
    public function create(string $access, string $note, string $createdBy, int $executionLimit) : string
    {
        $accessAllowedValues = ['read', 'write'];
        if (!in_array($access, $accessAllowedValues, true)) {
            throw new \InvalidArgumentException('Invalid access level provided. Allowed values are: ' . implode(', ', $accessAllowedValues));
        }
        $pdo = $this->db->getConnection();
        $apiKey = bin2hex(random_bytes(32)); // Generate a random API key
        $query = "INSERT INTO {$this->table} (api_key, access, note, created_by, executions_limit) VALUES (:apiKey, :access, :note, :createdBy, :executionLimit)";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':apiKey', $apiKey);
        $stmt->bindValue(':access', $access);
        $stmt->bindValue(':note', $note);
        $stmt->bindValue(':createdBy', $createdBy);
        $stmt->bindValue(':executionLimit', $executionLimit, \PDO::PARAM_INT);
        try {
            $stmt->execute();
            return $apiKey; // Return the generated API key
        } catch (\PDOException $e) {
            // Handle exception
            if (ERROR_VERBOSE) {
                throw new \Exception('Database error: ' . $e->getMessage(), 500);
            } else {
                throw new \Exception('Database error', 500);
            }
        }
    }
    public function incrementExecutionCount(string $apiKey, int $executions = 1): void
    {
        $pdo = $this->db->getConnection();
        $query = "
            UPDATE {$this->table}
            SET 
                executions = executions + :executions,
                executions_total = executions_total + :executions
            WHERE api_key = :apiKey
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':apiKey', $apiKey);
        $stmt->bindValue(':executions', $executions, \PDO::PARAM_INT);

        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            // Handle exception
            if (ERROR_VERBOSE) {
                throw new \Exception('Database error: ' . $e->getMessage(), 500);
            } else {
                throw new \Exception('Database error', 500);
            }
        }
    }
    public function getAccessLogsPerApiKey(string $apiKey): array
    {
        $pdo = $this->db->getConnection();
        $query = "SELECT * FROM api_access_log WHERE api_key = :apiKey ORDER BY date_created DESC";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':apiKey', $apiKey);
        try {
            $stmt->execute();
            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $logs ?: [];
        } catch (\PDOException $e) {
            // Handle exception
            if (ERROR_VERBOSE) {
                throw new \Exception('Database error: ' . $e->getMessage(), 500);
            } else {
                throw new \Exception('Database error', 500);
            }
        }
    }
    public function delete(string $apiKey): bool
    {
        $pdo = $this->db->getConnection();
        $query = "DELETE FROM {$this->table} WHERE api_key = :apiKey";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':apiKey', $apiKey);
        try {
            $stmt->execute();
            return $stmt->rowCount() > 0; // Return true if a row was deleted
        } catch (\PDOException $e) {
            // Handle exception
            if (ERROR_VERBOSE) {
                throw new \Exception('Database error: ' . $e->getMessage(), 500);
            } else {
                throw new \Exception('Database error', 500);
            }
        }
    }
}