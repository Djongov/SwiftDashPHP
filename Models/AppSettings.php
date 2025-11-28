<?php

declare(strict_types=1);

// Path: Models/AppSettings.php

// Called in /Controllers/AppSettings.php

// Responsible for handling the AppSettings table in the database CRUD operations

namespace Models;

use App\Database\DB;
use App\Exceptions\AppSettingsException;
use App\Logs\SystemLog;
use Models\BasicModel;

class AppSettings extends BasicModel
{
    private $_table = 'app_settings';
    private $_mainColumn = 'name';
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
     * Checks if an IP exists in the AppSettings table, accepts an ID or an IP in CIDR notation
     * @category   Models - AppSettings
     * @author     @Djongov <djongov@gamerz-bg.com>
     * @param      string|int $param the id or the ip in CIDR notation
     * @return     string bool
     */
    public function exists(string|int $param): bool
    {
        // If the parameter is an integer, we assume it's an ID
        if (is_int($param)) {
            $query = "SELECT $this->_mainColumn FROM $this->_table WHERE id = ?";
        } else {
            $query = "SELECT $this->_mainColumn FROM $this->_table WHERE $this->_mainColumn = ?";
        }

        $stmt = $this->_db->getConnection()->prepare($query);
        $stmt->execute([$param]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $rowCount = count($rows);
        return ($rowCount > 0) ? true : false;
    }
    public function getAll(): array
    {
        $pdo = $this->_db->getConnection();
        $stmt = $pdo->query("SELECT * FROM $this->_table");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getAllByOwner(string $owner): array
    {
        $pdo = $this->_db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM $this->_table WHERE owner = ?");
        $stmt->execute([$owner]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    /**
     * Gets an IP from the AppSettings table, accepts an ID or an IP in CIDR notation. If no parameter is provided, returns all IPs
     * @category   Models - AppSettings
     * @author     @Djongov <djongov@gamerz-bg.com>
     * @param      string|int $param the id or the ip in CIDR notation
     * @return     array returns the IP data as an associative array and if no parameter is provided, returns fetch_all
     * @throws     AppSettingsException, IPDoesNotExist, InvalidIP from formatIp
     */
    public function get(string|int|null $param = null, ?string $sort = null, ?int $limit = null, ?string $orderBy = null): array
    {
        $pdo = $this->_db->getConnection();

        if (!$param) {
            $query = "SELECT * FROM $this->_table";
            $query = self::applySortingAndLimiting($query, $orderBy, $sort, $limit);
            $stmt = $pdo->query($query);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        if (is_int($param)) {
            if (!$this->exists($param)) {
                throw (new AppSettingsException())->settingDoesNotExist();
            }
            $stmt = $pdo->prepare("SELECT * FROM $this->_table WHERE id = ?");
            $stmt->execute([$param]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } else {
            if (!$this->exists($param)) {
                throw (new AppSettingsException())->settingDoesNotExist();
            }
            $stmt = $pdo->prepare("SELECT * FROM $this->_table WHERE $this->_mainColumn = ?");
            $stmt->execute([$param]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
    }
    /**
     * Saves an IP to the AppSettings table, accepts an IP in CIDR notation, the user who created the IP and an optional comment
     * @category   Models - AppSettings
     * @author     @Djongov <djongov@gamerz-bg.com>
     * @param      string $name the name of the setting
     * @param      string $value the value of the setting
     * @param      string $type the type of the setting
     * @param      string $owner the owner/creator of the setting
     * @param      bool $adminSetting whether this is an admin-only setting
     * @param      string|null $description optional description of the setting
     * @return     int
     * @throws     AppSettingsException settingAlreadyExists, notSaved, InvalidSetting from formatSetting
     * @system_log       Setting added to the AppSettings table, by who and under which id
     */
    public function create(string $name, string $value, string $type, string $owner, bool $adminSetting = false, ?string $description = null): int
    {
        // Check if IP exists
        if ($this->exists($name)) {
            throw (new AppSettingsException())->alreadyExists();
        }
        $pdo = $this->_db->getConnection();
        $stmt = $pdo->prepare("INSERT INTO $this->_table ($this->_mainColumn, value, type, owner, admin_setting, description) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$name, $value, $type, $owner, $adminSetting ? 1 : 0, $description]);
        if ($stmt->rowCount() === 0) {
            SystemLog::write('Setting not created with name ' . $name . ', value ' . $value . ', and type ' . $type, 'AppSettings');
            throw (new AppSettingsException())->notSaved();
        }

        $settingId = (int) $pdo->lastInsertId();
        SystemLog::write('Setting created with ID ' . $settingId . ' by ' . $owner . ' and data name: ' . $name . ', value: ' . $value . ', type: ' . $type . ', admin_setting: ' . ($adminSetting ? 'true' : 'false') . ', description: ' . ($description ?? 'none'), 'AppSettings');

        return $settingId;
    }
    /**
     * Updates an IP in the AppSettings table, accepts an associative array with the data to update, the id of the IP and the user who updates the IP, if the IP does not exist, throws an exception
     * @category   Models - AppSettings
     * @author     @Djongov <djongov@gamerz-bg.com>
     * @param      array $data an associative array with the data to update, needs to comply with the columns in the table
     * @param      int $id the id of the IP
     * @param      string $updatedBy the user who updates the setting
     * @return     bool
     * @throws     AppSettingsException settingDoesNotExist, notSaved
     * @system_log Setting updated and by who and what data was passed
     */
    public function update(array $data, int $id, string $updatedBy): bool
    {
        // Check if the data matches the columns

        $this->_db->checkDBColumnsAndTypes($data, $this->_table);

        if (!$this->exists($id)) {
            throw (new AppSettingsException())->settingDoesNotExist();
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
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        if ($stmt->rowCount() === 1) {
            SystemLog::write('Setting with id ' . $id . ' updated by ' . $updatedBy . ' with data ' . json_encode($data), 'AppSettings');
            return true;
        } else {
            throw (new AppSettingsException())->notSaved('Setting not saved');
        }
    }
    /**
     * Deletes an IP in the AppSettings table, accepts the id of the IP and the user who deletes the IP, if the IP does not exist, throws an exception
     * @category   Models - AppSettings
     * @author     @Djongov <djongov@gamerz-bg.com>
     * @param      int $id the id of the IP
     * @param      string $deletedBy the user who deletes the setting
     * @return     bool
     * @throws     AppSettingsException settingDoesNotExist, notSaved
     * @system_log Setting deleted and by who
     */
    public function delete(int $id, string $deletedBy): bool
    {
        // Check if setting exists
        if (!$this->exists($id)) {
            throw (new AppSettingsException())->settingDoesNotExist();
        }
        // We only know the id, so just for logging purposes, we will pull the setting
        $setting = $this->get($id)[$this->_mainColumn];
        $pdo = $this->_db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM $this->_table WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 1) {
            SystemLog::write('Setting ' . $setting . ' (id ' . $id . ') deleted by ' . $deletedBy, 'AppSettings');
            return true;
        } else {
            throw (new AppSettingsException())->notSaved('Setting ' . $setting . ' not deleted');
        }
    }
}
