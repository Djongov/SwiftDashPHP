<?php

declare(strict_types=1);

namespace Models;

use App\Database\DB;

class BasicModel
{
    protected DB $_db;

    public function __construct()
    {
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
}
