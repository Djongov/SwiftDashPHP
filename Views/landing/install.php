<?php

declare(strict_types=1);

use App\Install;
use Components\Alerts;

try {
    // Default PDO options
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    switch (DB_DRIVER) {
        case 'mysql':
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
            if (defined("DB_SSL") && DB_SSL) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = DB_CA_CERT;
            }
            break;

        case 'pgsql':
            $dsn = 'pgsql:host=' . DB_HOST . ';dbname=' . DB_NAME;
            if (defined("DB_SSL") && DB_SSL) {
                $dsn .= ';sslmode=require;sslrootcert=' . DB_CA_CERT;
            }
            break;

        case 'sqlite':
            $dsn = 'sqlite:' . ROOT . '/.tools/' . DB_NAME . '.db';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            // Verify SQLite schema
            $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' LIMIT 1");
            if ($result->fetchColumn() === false) {
                $install = new Install();
                echo $install->start();
                return;
            }
            break;

        default:
            throw new Exception('Unsupported DB_DRIVER: ' . DB_DRIVER);
    }

    // Create PDO object if not already initialized for SQLite
    $pdo ??= new PDO($dsn, DB_USER, DB_PASS, $options);
    echo Alerts::info('Successfully connected to the database. Nothing to do here.');
} catch (PDOException $e) {
    $error = $e->getMessage();
    try {
        $install = new Install();
        echo $install->start();
    } catch (Exception $e) {
        echo Alerts::danger($e->getMessage());
    }
} catch (Exception $e) {
    echo Alerts::danger($e->getMessage());
}
