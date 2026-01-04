<?php

declare(strict_types=1);

namespace Models;

use App\Database\DB;

class APIKeys extends BasicModel
{
    protected DB $_db;
    protected string $_table = 'api_keys';
    protected string $_mainColumn = 'api_key';

    public function __construct(?string $table = null)
    {
        parent::__construct($this->_table);
        $this->setter($this->_table, $this->_mainColumn);
    }

    public function getApiKeyByNote(string $note): ?string
    {
        $pdo = $this->_db->getConnection();
        $query = "SELECT api_key FROM {$this->_table} WHERE note = :note";
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
    public function incrementExecutionCount(string $apiKey, int $executions = 1): void
    {
        $pdo = $this->_db->getConnection();
        $query = "
            UPDATE {$this->_table}
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
        $pdo = $this->_db->getConnection();
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
}
