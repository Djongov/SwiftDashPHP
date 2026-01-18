<?php

declare(strict_types=1);

namespace Models;

use App\Database\DB;
use App\Exceptions\BasicModelExceptions;
use App\Logs\SystemLog;

class BasicModel implements BasicModelInterface
{
    private $_table = '';
    private $_mainColumn = '';
    protected DB $_db;

    public function __construct(?string $table = null)
    {
        $this->_table = $table ?? $this->_table;
        $this->_db = new DB();
    }

    // get columns from the table
    public function getColumns(string $table): array
    {
        $describeArray = $this->_db->describe($table);
        $columns = [];
        foreach ($describeArray as $column => $type) {
            array_push($columns, $column);
        }
        return $columns;
    }
    public function rowCount(): int
    {
        $pdo = $this->_db->getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $this->_table");
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)$result['count'];
    }
    public static function applySortingAndLimiting(string $query, ?string $orderBy = null, ?string $sort = null, ?int $limit = null): string
    {
        if ($orderBy) {
            $query .= " ORDER BY $orderBy " . ($sort ?? "ASC");
        }

        if ($limit) {
            $query .= " LIMIT $limit";
        }

        return $query;
    }
    public function setter($table, $mainColumn): void
    {
        $this->_table = $table;
        $this->_mainColumn = $mainColumn;
    }
    /**
     * Checks if an IP exists in the firewall table, accepts an ID or an IP in CIDR notation
     * @category   Models - Firewall
     * @author     @Djongov <djongov@gamerz-bg.com>
     * @param      string|int $param the id or the mainColumn value
     * @return     string bool
     */
    public function exists(string|int $param): bool
    {
        // If the parameter is an integer, we assume it's an ID
        if (is_int($param)) {
            $query = "SELECT * FROM $this->_table WHERE id = ?";
        } else {
            $query = "SELECT * FROM $this->_table WHERE $this->_mainColumn = ?";
        }
        $stmt = $this->_db->getConnection()->prepare($query);
        $stmt->execute([$param]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $rowCount = count($rows);
        return ($rowCount > 0) ? true : false;
    }
    public function getAll(
        ?array $where = null,
        ?string $orderBy = null,
        ?string $sort = null,
        ?int $limit = null
    ): array {
        $pdo = $this->_db->getConnection();
        $query = "SELECT * FROM {$this->_table}";
        $params = [];

        if ($where) {
            [$whereSql, $params] = self::buildWhere($where);
            $query .= $whereSql;
        }

        $query = self::applySortingAndLimiting($query, $orderBy, $sort, $limit);

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function get(string|int|null $param = null, ?string $sort = null, ?int $limit = null, ?string $orderBy = null): array
    {
        $pdo = $this->_db->getConnection();

        if (is_int($param)) {
            if (!$this->exists($param)) {
                throw (new BasicModelExceptions())->notFound();
            }
            $stmt = $pdo->prepare("SELECT * FROM $this->_table WHERE id = ?");
            $stmt->execute([$param]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } else {
            if (!$this->exists($param)) {
                throw (new BasicModelExceptions())->notFound();
            }
            $stmt = $pdo->prepare("SELECT * FROM $this->_table WHERE $this->_mainColumn = ?");
            $stmt->execute([$param]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
    }
    private static function buildWhere(array $where): array
    {
        $clauses = [];
        $params  = [];

        foreach ($where as $column => $value) {
            // Handle array values with IN clause
            if (is_array($value)) {
                $placeholders = [];
                foreach ($value as $i => $val) {
                    $param = ':' . $column . '_' . $i;
                    $placeholders[] = $param;
                    $params[$param] = $val;
                }
                $clauses[] = "$column IN (" . implode(', ', $placeholders) . ")";
            } else {
                // Handle single value with = operator
                $param = ':' . $column;
                $clauses[] = "$column = $param";
                $params[$param] = $value;
            }
        }

        return [' WHERE ' . implode(' AND ', $clauses), $params];
    }
    public function create(array $data): int
    {
        $pdo = $this->_db->getConnection();

        if (empty($data)) {
            throw (new BasicModelExceptions())->emptyData();
        }

        $this->_db->checkDBColumnsAndTypes($data, $this->_table);

        $columns = array_keys($data);

        $dbColumns = $this->getColumns($this->_table);

        // if created_by column exists, let's figure out who is creating the entry
        if (in_array('created_by', $dbColumns) && empty($data['created_by'])) {
            $user = \App\Authentication\JWT::extractUserName(\App\Authentication\AuthToken::get());
            if (!empty($user)) {
                $data['created_by'] = $user;
                if (!in_array('created_by', $columns)) {
                    $columns[] = 'created_by';
                }
            }
        }

        $placeholders = array_fill(0, count($columns), '?');

        $query = "INSERT INTO $this->_table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute(array_values($data));
        } catch (\PDOException $e) {
            // Skip throwing exception if the error is database unknown
            throw (new BasicModelExceptions())->genericError(
                'DB INSERT FAILED: ' . $e->getMessage(),
                500
            );
        }

        return (int)$pdo->lastInsertId();
    }
    public function update(array $data, int $id): int
    {
        // Check if the data matches the columns

        $this->_db->checkDBColumnsAndTypes($data, $this->_table);

        if (!$this->exists($id)) {
            throw (new BasicModelExceptions())->notFound();
        }

        $columns = array_keys($data);

        $dbColumns = $this->getColumns($this->_table);

        // if created_by column exists, let's figure out who is creating the entry
        if (in_array('last_updated_by', $dbColumns) && empty($data['last_updated_by'])) {
            $user = \App\Authentication\JWT::extractUserName(\App\Authentication\AuthToken::get());
            if (!empty($user)) {
                $data['last_updated_by'] = $user;
                if (!in_array('last_updated_by', $columns)) {
                    $columns[] = 'last_updated_by';
                }
            }
        }

        // Check if data is correct
        $sql = "UPDATE $this->_table SET ";
        $updates = [];
        // Check if all keys in $array match the columns
        foreach ($data as $key => $value) {
            // Add the column to be updated to the SET clause
            $updates[] = "$key = ?";
        }
        // Combine the SET clauses with commas
        $sql .= implode(', ', $updates);

        // Add a WHERE clause to specify which organization to update
        $sql .= " WHERE id = ?";

        // Prepare and execute the query using queryPrepared
        $values = array_values($data);
        $values[] = $id; // Add the id for the WHERE clause
        $pdo = $this->_db->getConnection();

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw (new BasicModelExceptions())->genericError($e->getMessage(), 500);
        }
    }
    public function delete(int $id): bool
    {
        // Check if entry exists
        if (!$this->exists($id)) {
            throw (new BasicModelExceptions())->notFound();
        }

        $pdo = $this->_db->getConnection();
        $query = "DELETE FROM $this->_table WHERE id = ?";
        $stmt = $pdo->prepare($query);
        try {
            $stmt->execute([$id]);
            return true;
        } catch (\PDOException $e) {
            if ($e->getCode() === 'HY000') {
                return false;
            }
            throw (new BasicModelExceptions())->genericError($e->getMessage(), 500);
        }
    }
    public function deleteAll(): bool
    {
        $pdo = $this->_db->getConnection();
        $query = "DELETE FROM $this->_table";
        $stmt = $pdo->prepare($query);
        try {
            $stmt->execute();
            return true;
        } catch (\PDOException $e) {
            if ($e->getCode() === 'HY000') {
                return false;
            }
            throw (new BasicModelExceptions())->genericError($e->getMessage(), 500);
        }
    }
}
