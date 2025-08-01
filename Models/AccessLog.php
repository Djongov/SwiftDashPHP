<?php

declare(strict_types=1);

// Path: Models/AccessLog.php

// Called in /Controllers/AccessLog.php

// Responsible for handling the AccessLog table in the database CRUD operations

namespace Models;

use App\Database\DB;
use App\Exceptions\AccessLogException;
use App\Logs\SystemLog;
use Models\BasicModel;

class AccessLog extends BasicModel
{
    private $_table = 'api_access_log';
    private $_mainColumn = 'request_id';
    protected DB $_db;

    public function __construct()
    {
        $this->_db = new DB();
    }

    public function setter($table, $mainColumn): void
    {
        $this->_table = $table;
        $this->_mainColumn = $mainColumn;
    }
    /**
     * Checks if an IP exists in the AccessLog table, accepts an ID or an IP in CIDR notation
     * @category   Models - AccessLog
     * @author     @Djongov <djongov@gamerz-bg.com>
     * @param      string|int $param the id or the ip in CIDR notation
     * @return     string bool
     */
    public function exists(string|int $param): bool
    {
        // Determine if we're querying by ID or column
        $query = is_int($param)
            ? "SELECT 1 FROM $this->_table WHERE id = ? LIMIT 1"
            : "SELECT 1 FROM $this->_table WHERE $this->_mainColumn = ? LIMIT 1";

        // Prepare and execute the statement
        $stmt = $this->_db->getConnection()->prepare($query);
        $stmt->execute([$param]);

        // Fetch a single row and check if it exists
        return $stmt->fetch() !== false;
    }
    /**
     * Gets an IP from the AccessLog table, accepts an ID or an IP in CIDR notation. If no parameter is provided, returns all IPs
     * @category   Models - AccessLog
     * @author     @Djongov <djongov@gamerz-bg.com>
     * @param      string|int $param the id or the ip in CIDR notation
     * @return     array returns the IP data as an associative array and if no parameter is provided, returns fetch_all
     * @throws     AccessLogException
     */
    public function get(string|int|null $param = null, ?string $sort = null, ?int $limit = null, ?string $orderBy = null): array
    {
        $pdo = $this->_db->getConnection();
        // if the parameter is empty, we assume we want all the IPs
        if (!$param) {
            $query = "SELECT * FROM $this->_table";
            $query = self::applySortingAndLimiting($query, $orderBy, $sort, $limit);
            $stmt = $pdo->query($query);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        // If the parameter is an integer, we assume it's an ID
        if (is_int($param)) {
            if (!$this->exists($param)) {
                throw (new AccessLogException())->genericError('access log ' . $param . ' does not exist', 404);
            }
            $stmt = $pdo->prepare("SELECT * FROM $this->_table WHERE id = ?");
            $stmt->execute([$param]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } else {
            // Check if IP exists
            if (!$this->exists($param)) {
                throw (new AccessLogException())->genericError('access log ' . $param . ' does not exist', 404);
            }
            $stmt = $pdo->prepare("SELECT * FROM $this->_table WHERE $this->_mainColumn = ?");
            $stmt->execute([$param]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
    }
    public function add(array $data): void
    {
        $pdo = $this->_db->getConnection();
        $dataValues = array_values($data);
        $query = 'INSERT INTO ' . $this->_table . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ')';
        $stmt = $pdo->prepare($query);
        try {
            $stmt->execute($dataValues);
        } catch (\Exception $e) {
            throw (new AccessLogException())->genericError($e->getMessage(), 400);
        }
    }
    public function delete(int $id, string $deletedBy): bool
    {
        // Check if IP exists
        if (!$this->exists($id)) {
            throw (new AccessLogException())->genericError('ID ' . $id . ' does not exist', 404);
        }
        $pdo = $this->_db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM $this->_table WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 1) {
            SystemLog::write('API access log with id ' . $id . ' deleted', 'API Access Log');
            return true;
        } else {
            throw (new AccessLogException())->notDeleted();
        }
    }
    public function deleteAll(): bool
    {
        $pdo = $this->_db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM $this->_table");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            SystemLog::write('All API access logs deleted', 'API Access Log');
            return true;
        } else {
            throw (new AccessLogException())->notDeleted();
        }
    }
}
