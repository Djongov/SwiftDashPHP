<?php

declare(strict_types=1);

namespace Models;

use App\Database\DB;

class UtmCapturer extends BasicModel
{
    private $_table = 'utm_captures';
    private $_mainColumn = 'ip_cidr';
    protected DB $_db;

    public function __construct()
    {
        $this->_db = new DB();
    }
    public function create(array $data): int
    {
        $pdo = $this->_db->getConnection();

        $this->_db->checkDBColumnsAndTypes($data, $this->_table);

        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $stmt = $pdo->prepare("INSERT INTO $this->_table ($columns) VALUES ($placeholders)");
        try {
            $stmt->execute(array_values($data));
            return (int)$pdo->lastInsertId();
        } catch (\Exception $e) {
            throw new \Exception("Failed to create UTM capture: " . $e->getMessage());
        }
    }

}
