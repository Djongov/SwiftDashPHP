<?php

declare(strict_types=1);

namespace App\Database;

use App\Utilities\General;
use App\Api\Response;

class DB
{
    private $_pdo;

    public function __construct(
        ?string $host = DB_HOST,
        ?string $username = DB_USER,
        ?string $password = DB_PASS,
        string $database = DB_NAME,
        ?int $port = DB_PORT,
        string $driver = DB_DRIVER
    )
    {
        $config = [
            'driver' => $driver,
            'host' => $host,
            'dbname' => $database,
            'username' => $username,
            'password' => $password,
            'port' => $port,
            'driver' => $driver
        ];
        try {
            $this->connect($config);
        } catch (\Exception $e) {
            if (!ERROR_VERBOSE) {
                throw new \PDOException('Database connection error');
            } else {
                throw new \PDOException($e->getMessage());
            }
        }
    }

    private function connect(array $config): void
    {
        if ($config['driver'] === 'sqlite') {
            $dbFilePath = dirname($_SERVER['DOCUMENT_ROOT']) . '/.tools/' . $config['dbname'] . '.db';
            if (!file_exists($dbFilePath)) {
                error_log("DB: SQLite database file does not exist: " . $config['dbname']);
                throw new \PDOException("DB: SQLite database file does not exist: " . $config['dbname']);
            }
        }

        $dsn = $this->buildDsn($config);
        $options = $this->getPDOOptions();

        try {
            $this->_pdo = new \PDO($dsn, $config['username'], $config['password'], $options);
            $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            if (ERROR_VERBOSE) {
                throw new \PDOException("DB: PDO connection failed: " . $e->getMessage());
            } else {
                Response::output('Database connection failed', 500);
            }
            throw $e;
        } catch (\Exception $e) {
            if (ERROR_VERBOSE) {
                throw new \PDOException("DB: PDO connection failed: " . $e->getMessage());
            } else {
                Response::output('Database connection failed', 500);
            }
        }
    }

    public function getConnection(): \PDO
    {
        if ($this->_pdo instanceof \PDO) {
            return $this->_pdo;
        } else {
            throw new \PDOException("DB: Database connection has not been established.");
        }
    }

    private function buildDsn(array $config): string
    {
        $dsn = "{$config['driver']}:";
        $driver = $config['driver'];
        unset($config['driver'], $config['username'], $config['password']);

        if ($driver === 'sqlite') {
            $dsn .= dirname($_SERVER['DOCUMENT_ROOT']) . '/.tools/' . $config['dbname'] . '.db';
        } else {
            foreach ($config as $key => $value) {
                $dsn .= "$key=$value;";
            }

            // Add SSL options if enabled
            if (defined("DB_SSL") && DB_SSL) {
                $dsn .= "sslmode=require;";
                // Add CA certificate path
                $dsn .= "sslrootcert=" . DB_CA_CERT . ";";
            }
        }

        return $dsn;
    }


    private function getPDOOptions(): array
    {
        // You can add any default PDO options here if needed
        $options = [];
        if (defined("DB_SSL") && DB_SSL) {
            $options[\PDO::MYSQL_ATTR_SSL_CA] = DB_CA_CERT;
        }
        $options[\PDO::ATTR_EMULATE_PREPARES] = false;
        $options[\PDO::ATTR_TIMEOUT] = 15;
        $options[\Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT] = false;
        return $options;
    }

    public function executeQuery(\PDO $_pdo, string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $_pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            if (ERROR_VERBOSE) {
                throw new \PDOException("Error executing query: " . $e->getMessage() . ' SQL: ' . $sql . ' Params: ' . json_encode($params) . ' Error Code: ' . $e->getCode());
            } else {
                throw new \PDOException("Error executing query");
            }
        } catch (\Exception $e) {
            if (ERROR_VERBOSE) {
                throw new \PDOException("Error executing query: " . $e->getMessage() . ' SQL: ' . $sql . ' Params: ' . json_encode($params) . ' Error Code: ' . $e->getCode());
            } else {
                throw new \PDOException("Error executing query");
            }
        }
    }

    public function __destruct()
    {
        $this->_pdo = null;
    }

    public function multiQuery(array $queryArray): void
    {
        try {
            $_pdo = $this->getConnection();
            $_pdo->beginTransaction();

            foreach ($queryArray as $query) {
                $_pdo->exec($query);
            }

            $_pdo->commit();
        } catch (\PDOException $e) {
            // If an error occurs, roll back the transaction
            $_pdo->rollBack();

            // Handle the exception, log it, or throw a custom exception
            throw new \PDOException("Error executing multiple queries: " . $e->getMessage());
        }
    }

    public function checkDBColumns(array $columns, string $table): void
    {
        $dbTableArray = $this->describe($table);

        // Extract column names from the table structure
        $dbColumns = [];
        foreach ($dbTableArray as $row => $type) {
            array_push($dbColumns, $row);
        }

        // Check if all columns in the input array exist in the database
        foreach ($columns as $column) {
            if (!in_array($column, $dbColumns)) {
                throw new \Exception("Column '$column' does not exist in table '$table'");
            }
        }
    }

    public function checkDBColumnsAndTypes(array $array, string $table): void
    {
        $dbTableArray = $this->describe($table); // returns ['column_name' => ['type' => '...', 'nullable' => true|false]]

        foreach ($array as $column => $data) {
            if (!array_key_exists($column, $dbTableArray)) {
                throw new \Exception("Column '$column' does not exist in table '$table'");
            }

            $expectedType = self::normalizeDataType($dbTableArray[$column]['type']);
            $isNullable = $dbTableArray[$column]['nullable'] ?? false;

            // If the value is NULL and the column is nullable, skip type checking
            if (is_null($data) && $isNullable) {
                continue;
            }

            // Determine actual data type
            if (is_string($data)) {
                $dataType = General::isDateOrDatetime($data) ? 'datetime' : 'string';
            } elseif (in_array($data, ['0', '1', 'true', 'false', true, false, 1, 0], true)) {
                $dataType = $this->isBooleanColumn($expectedType) ? 'bool' : 'int';
            } elseif (is_numeric($data)) {
                $dataType = 'int';
            } else {
                $dataType = gettype($data);
            }

            if ($dataType !== $expectedType) {
                throw new \Exception("Data type mismatch for column '$column'. Expected '$expectedType', got '$dataType'");
            }
        }
    }

    private function isBooleanColumn(string $expectedType): bool
    {
        if (DB_DRIVER === 'mysql' && $expectedType === 'tinyint(1)') {
            return true;
        }
        if ((DB_DRIVER === 'pgsql' || DB_DRIVER === 'sqlite') && $expectedType === 'boolean') {
            return true;
        }
        // and bool
        if ($expectedType === 'bool') {
            return true;
        }
        return false;
    }

    private static function normalizeDataType($type): string
    {
        // Handle ENUM types - they should be treated as strings
        if (str_starts_with(strtolower($type), 'enum(')) {
            return 'string';
        }

        // Convert common MySQL/PostgreSQL data types to PHP types
        $typeMap = [
            'tinyint' => 'int',
            'smallint' => 'int',
            'mediumint' => 'int',
            'int' => 'int',
            'integer' => 'int',
            'bigint' => 'int',
            'decimal' => 'float',
            'float' => 'float',
            'double' => 'float',
            'real' => 'float', // PostgreSQL specific
            'date' => 'datetime',
            'datetime' => 'datetime',
            'timestamp' => 'datetime',
            'timestamp without time zone' => 'datetime', // PostgreSQL specific
            'time' => 'datetime',
            'year' => 'datetime',
            'char' => 'string',
            'varchar' => 'string',
            'character varying' => 'string', // PostgreSQL specific
            'text' => 'string',
            'json' => 'string',
            'boolean' => 'bool',

            // SQLite specific
            'boolean' => 'bool', // SQLite uses 'boolean' for bool type

            // Adjust this based on SQLite specific types
            'tinyint(' => 'int', // Adjusted to match SQLite's int type handling

            // Add more mappings as needed
        ];

        // Normalize data type based on the provided $type
        foreach ($typeMap as $dbType => $phpType) {
            if (str_starts_with(strtolower($type), $dbType)) {
                return $phpType;
            }
        }

        return $type; // Return original type if no match found
    }


    public function mapDataTypesArray(string $value): string
    {
        $type = '';
        $value = strtolower($value);

        // SQLite data type mappings
        if (str_starts_with($value, 'integer') || str_starts_with($value, 'int')) {
            $type = 'int';
        }
        if (str_starts_with($value, 'real') || str_starts_with($value, 'float') || str_starts_with($value, 'double') || str_starts_with($value, 'numeric') || str_starts_with($value, 'decimal')) {
            $type = 'float';
        }
        if (str_starts_with($value, 'text') || str_starts_with($value, 'char') || str_starts_with($value, 'varchar')) {
            $type = 'string';
        }
        if (str_starts_with($value, 'blob')) {
            $type = 'blob'; // BLOB type in SQLite
        }
        if (str_starts_with($value, 'date') || str_starts_with($value, 'time') || str_starts_with($value, 'timestamp')) {
            $type = 'datetime'; // SQLite stores date/time as text or numeric
        }
        if (str_starts_with($value, 'boolean') || str_starts_with($value, 'bool') || str_starts_with($value, 'tinyint(1)')) {
            $type = 'bool'; // BOOLEAN type
        }
        // Json
        if (str_starts_with($value, 'json')) {
            $type = 'json';
        }

        // Additional considerations specific to your application or SQLite usage

        return $type;
    }
    public function getDriver(): string
    {
        $_pdo = $this->getConnection();
        return $_pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    public function getTableNames(): array
    {
        $_pdo = $this->getConnection();
        $driver = $this->getDriver();
        $dbTables = [];
        try {
            switch ($driver) {
                case 'mysql':
                    $sql = "SHOW TABLES";
                    break;
                case 'pgsql':
                    $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
                    break;
                case 'sqlite':
                    $sql = "SELECT name FROM sqlite_master WHERE type='table'";
                    break;
                default:
                    throw new \Exception("Unsupported database driver: $driver");
            }

            $stmt = $_pdo->prepare($sql);
            $stmt->execute();

            // Fetch table names based on the database driver
            switch ($driver) {
                case 'mysql':
                    while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                        $dbTables[] = $row[0];
                    }
                    break;
                case 'pgsql':
                    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                        $dbTables[] = $row['table_name'];
                    }
                    break;
                case 'sqlite':
                    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                        $dbTables[] = $row['name'];
                    }
                    break;
                default:
                    throw new \Exception("Unsupported database driver: $driver");
            }

            return $dbTables;
        } catch (\PDOException $e) {
            if (ERROR_VERBOSE) {
                throw new \PDOException("Error fetching table names: " . $e->getMessage());
            } else {
                throw new \PDOException("Error fetching table names");
            }
        } catch (\Exception $e) {
            if (ERROR_VERBOSE) {
                throw new \PDOException("Error fetching table names: " . $e->getMessage());
            } else {
                throw new \PDOException("Error fetching table names");
            }
        }
    }
    public function getTableNamesAndSizes(): array
    {
        $_pdo = $this->getConnection();
        $driver = $this->getDriver();
        $dbTables = [];

        try {
            switch ($driver) {
                case 'mysql':
                    $sql = "
                        SELECT 
                            table_name AS name,
                            ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
                        FROM information_schema.tables
                        WHERE table_schema = DATABASE()
                    ";
                    break;

                case 'pgsql':
                    $sql = "
                        SELECT 
                            relname AS name,
                            pg_total_relation_size(relid) AS size_bytes
                        FROM pg_catalog.pg_statio_user_tables
                    ";
                    break;

                case 'sqlite':
                $tables = $_pdo->query("
                    SELECT name 
                    FROM sqlite_master 
                    WHERE type='table' AND name NOT LIKE 'sqlite_%'
                ")->fetchAll(\PDO::FETCH_COLUMN);

                foreach ($tables as $table) {
                    $cols = $_pdo->query("PRAGMA table_info($table)")->fetchAll(\PDO::FETCH_ASSOC);
                    $colExpr = implode(' + ', array_map(fn($c) => "LENGTH(\"{$c['name']}\")", $cols));

                    if ($colExpr === '') {
                        $dbTables[$table] = '0 MB';
                        continue;
                    }

                    $sql = "SELECT SUM($colExpr) AS total_bytes FROM \"$table\"";
                    $size = $_pdo->query($sql)->fetch(\PDO::FETCH_ASSOC);
                    $dbTables[$table] = round(($size['total_bytes'] ?? 0) / 1024 / 1024, 2) . ' MB';
                }
                return $dbTables;
                    break;

                default:
                    throw new \Exception('Unsupported database driver: ' . $driver);
            }

            $stmt = $_pdo->prepare($sql);
            $stmt->execute();

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if ($driver === 'mysql') {
                    $dbTables[$row['name']] = $row['size_mb'] . ' MB';
                } elseif ($driver === 'pgsql') {
                    $dbTables[$row['name']] = round($row['size_bytes'] / 1024 / 1024, 2) . ' MB';
                } elseif ($driver === 'sqlite') {
                    $dbTables[$row['name']] = round($row['size_mb'], 2) . ' MB';
                }
            }

            return $dbTables;

        } catch (\PDOException $e) {
            if (ERROR_VERBOSE) {
                throw new \PDOException('Error fetching table sizes: ' . $e->getMessage());
            } else {
                throw new \PDOException('Error fetching table sizes');
            }
        } catch (\Exception $e) {
            if (ERROR_VERBOSE) {
                throw new \PDOException('Error fetching table sizes: ' . $e->getMessage());
            } else {
                throw new \PDOException('Error fetching table sizes');
            }
        } catch (\Exception $e) {
            if (ERROR_VERBOSE) {
                throw new \PDOException('Error fetching table sizes: ' . $e->getMessage());
            } else {
                throw new \PDOException('Error fetching table sizes');
            }
        }
    }
    public function describe(string $table): array
    {
        $db = new self();
        $_pdo = $db->getConnection();
        $driver = $_pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $dbColumns = [];

        switch ($driver) {
            case 'mysql':
                $sql = "DESCRIBE $table";
                $stmt = $_pdo->prepare($sql);
                $stmt->execute();
                $dbTableArray = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($dbTableArray as $row) {
                    $dbColumns[$row['Field']] = [
                        'type' => $row['Type'],
                        'nullable' => strtoupper($row['Null']) === 'YES'
                    ];
                }
                break;

            case 'pgsql':
                $sql = "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = ?";
                $stmt = $_pdo->prepare($sql);
                $stmt->execute([$table]);
                $dbTableArray = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($dbTableArray as $row) {
                    $dbColumns[$row['column_name']] = [
                        'type' => $row['data_type'],
                        'nullable' => strtoupper($row['is_nullable']) === 'YES'
                    ];
                }
                break;

            case 'sqlite':
                $sql = "PRAGMA table_info($table)";
                $stmt = $_pdo->prepare($sql);
                $stmt->execute();
                $dbTableArray = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($dbTableArray as $row) {
                    $dbColumns[$row['name']] = [
                        'type' => $row['type'],
                        'nullable' => $row['notnull'] == 0
                    ];
                }
                break;

            default:
                throw new \Exception("Unsupported database driver: $driver");
        }

        return $dbColumns;
    }

}
