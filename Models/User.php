<?php

declare(strict_types=1);

namespace Models;

use App\Database\DB;
use App\Logs\SystemLog;
use App\Exceptions\UserExceptions;
use Models\BasicModel;

class User extends BasicModel
{
    public string $_table = 'users';
    public string $_mainColumn = 'username';
    protected DB $_db;

    public function __construct(?string $table = null)
    {
        parent::__construct($this->_table);
        $this->setter($this->_table, $this->_mainColumn);
    }

    // User get
    public function get(string|int|null $param = null, ?string $sort = null, ?int $limit = null, ?string $orderBy = null): array
    {
        $pdo = $this->_db->getConnection();
        if ($param === null) {
            // Let's pull all
            try {
                $query = "SELECT * FROM users";
                $query = self::applySortingAndLimiting($query, $orderBy, $sort, $limit);
                $result = $pdo->query($query);
                $array = $result->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                throw (new UserExceptions())->genericError($e->getMessage(), 500);
            }
            if (!$array) {
                throw (new UserExceptions())->userNotFound();
            } else {
                return $array;
            }
        }
        if (is_int($param)) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
                $stmt->execute([$param]);
                $array = $stmt->fetch(\PDO::FETCH_ASSOC);
                if (!$array) {
                    throw (new UserExceptions())->userNotFound();
                } else {
                    return $array;
                }
            } catch (\PDOException $e) {
                throw (new UserExceptions())->genericError($e->getMessage(), 500);
            }
        }
        // Finally, let's pull the api key by the api key
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
            $stmt->execute([$param]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw (new UserExceptions())->genericError($e->getMessage() . ' and query is: ', 500);
        }

        if (!$result) {
            if (ERROR_VERBOSE) {
                throw (new UserExceptions())->userNotFound();
            } else {
                throw (new UserExceptions())->genericError('Invalid username or password', 401);
            }
        }
        return $result;
    }
    // User creator
    public function create(array $data): int
    {
        unset($data['csrf_token']);
        unset($data['confirm_password']);

        $tableColumns = $this->getColumns($this->_table);

        // Now let's check if the structure of the data matches the table
        foreach ($data as $key => $value) {
            if (!in_array($key, $tableColumns)) {
                throw (new UserExceptions())->genericError('Invalid field ' . $key, 400);
            }
        }

        // First check if the user exists
        if ($this->exists($data['username'])) {
            throw (new UserExceptions())->userAlreadyExists();
        }

        // Prepare the password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Now let's check if the structure of the data matches the table
        $this->_db->checkDBColumnsAndTypes($data, 'users');

        $query = 'INSERT INTO users (';
        $columns = [];
        $values = [];
        foreach ($data as $key => $value) {
            $columns[] = "$key";
            $values[] = '?';
        }

        $query .= implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ')';

        $pdo = $this->_db->getConnection();
        $stmt = $pdo->prepare($query);
        $stmt->execute(array_values($data));

        if ($stmt->rowCount() === 0) {
            SystemLog::write('User not created with ' . json_encode($data), 'User API');
            throw (new UserExceptions())->userNotCreated();
        }

        $userId = (int) $pdo->lastInsertId();
        SystemLog::write('User created with ID ' . $userId . ' and data ' . json_encode($data), 'User API');

        return $userId;
    }
    // User updater
    public function update(array $data, int $id): int
    {
        // First let's check if the user exists
        if (!$this->exists($id)) {
            throw (new UserExceptions())->userNotFound();
        }

        $pdo = $this->_db->getConnection();

        $query = 'UPDATE users SET ';
        $updates = [];
        // Check if all keys in $reports_array match the columns
        foreach ($data as $key => $value) {
            // Add the column to be updated to the SET clause
            $updates[] = "$key = ?";
        }
        // Combine the SET clauses with commas
        $query .= implode(', ', $updates);

        // Add a WHERE clause to specify which organization to update
        $query .= " WHERE id = ?";

        // Prepare and execute the query using queryPrepared
        $values = array_values($data);
        $values[] = $id; // Add the username for the WHERE clause
        $stmt = $pdo->prepare($query);
        try {
            $stmt->execute(array_values($values));
            return $stmt->rowCount();
            SystemLog::write('User with id ' . $id . ' updated with ' . json_encode($data), 'User API');
        } catch (\PDOException $e) {
            if (ini_get('display_errors') === '1') {
                throw new \PDOException($e->getMessage());
            } else {
                throw (new UserExceptions())->genericError('Could not update user', 500);
            }
        }
    }
    // User Deleter
    public function delete(string|int $param): bool
    {
        if (!$this->exists($param)) {
            throw (new UserExceptions())->userNotFound();
        } else {
            if (is_string($param)) {
                $column = 'username';
            } else {
                $column = 'id';
            }
            $pdo = $this->_db->getConnection();
            $stmt = $pdo->prepare('DELETE FROM users WHERE ' . $column . ' =?');
            $stmt->execute([$param]);
            if ($stmt->rowCount() === 0) {
                throw (new UserExceptions())->userNotDeleted();
            } else {
                SystemLog::write('User with ' . $column . ' ' . $param . ' deleted', 'User API');

                return true;
            }
        }
    }
}
