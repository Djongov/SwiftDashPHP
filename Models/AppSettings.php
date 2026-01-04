<?php

declare(strict_types=1);

namespace Models;

use App\Database\DB;
use Models\BasicModel;

class AppSettings extends BasicModel
{
    private $_table = 'app_settings';
    private $_mainColumn = 'name';
    protected DB $_db;

    public function __construct(?string $table = null)
    {
        parent::__construct($this->_table);
        $this->setter($this->_table, $this->_mainColumn);
    }

    public function getAllByOwner(string $owner): array
    {
        $pdo = $this->_db->getConnection();
        $query = "SELECT * FROM $this->_table WHERE owner = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$owner]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
