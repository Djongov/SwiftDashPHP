<?php

declare(strict_types=1);

namespace App;

use Components\Alerts;
use Components\Html;
use App\Api\Response;
use App\Utilities\IP;
use App\Utilities\General;
use PDO;
use PDOException;

class Install
{
    public function start(): string
    {
        $html = '';
        $html .= Html::h2('Database does not exist, attempting to create it', true);

        // Connect to the database server without specifying the database
        try {
            $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
            if (DB_DRIVER === 'mysql') {
                $dsn = sprintf("mysql:host=%s;port=%d;charset=utf8mb4", DB_HOST, DB_PORT);
                if (defined("DB_SSL") && DB_SSL) {
                    $options[PDO::MYSQL_ATTR_SSL_CA] = DB_CA_CERT;
                }
            } elseif (DB_DRIVER === 'pgsql') {
                // Connect to a common default database, like 'postgres'
                $dsn = sprintf("pgsql:host=%s;port=%d;dbname=postgres", DB_HOST, DB_PORT);
                if (defined("DB_SSL") && DB_SSL) {
                    $dsn .= sprintf(";sslmode=require;sslrootcert=%s", DB_CA_CERT);
                }
            } elseif (DB_DRIVER === 'sqlite') {
                // Nothing to do, except pass the unsupported driver
            } else {
                throw new \Exception('Unsupported DB_DRIVER: ' . DB_DRIVER);
            }

            if (DB_DRIVER !== 'sqlite') {
                $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            }
        } catch (PDOException $e) {
            Response::output('Database connection error: ' . $e->getMessage(), 400);
        }

        // Create the database if it doesn't exist
        try {
            if (DB_DRIVER === 'mysql') {
                $conn->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            } elseif (DB_DRIVER === 'pgsql') {
                // PostgreSQL does not support the 'IF NOT EXISTS' clause for CREATE DATABASE
                $query = $conn->query("SELECT 1 FROM pg_database WHERE datname = '" . DB_NAME . "'");
                if (!$query->fetch()) {
                    $conn->exec("CREATE DATABASE \"" . DB_NAME . "\"");
                }
            } elseif (DB_DRIVER === 'sqlite') {
                // For sqlite we need to create the database file
                $dbDir = ROOT . '/.tools';
                $dbFile = '/' . DB_NAME . '.db';
                $dbFullPath = $dbDir . $dbFile;

                if (!is_writable($dbDir)) {
                    Response::output("Error: directory $dbDir is not writable.");
                }

                if (!file_exists($dbFullPath)) {
                    $conn = new PDO('sqlite:' . $dbFullPath);
                }
            }
        } catch (PDOException $e) {
            Response::output('Database creation error: ' . $e->getMessage(), 400);
        }
        // Reconnect to the newly created database
        try {
            if (DB_DRIVER === 'mysql') {
                $dsnWithDb = sprintf("mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4", DB_HOST, DB_PORT, DB_NAME);
            } elseif (DB_DRIVER === 'pgsql') {
                $dsnWithDb = sprintf("pgsql:host=%s;port=%d;dbname=%s", DB_HOST, DB_PORT, DB_NAME);
                if (defined("DB_SSL") && DB_SSL) {
                    $dsnWithDb .= sprintf(";sslmode=require;sslrootcert=%s", DB_CA_CERT);
                }
            } elseif (DB_DRIVER === 'sqlite') {
                $dsnWithDb = 'sqlite:' . ROOT . '/.tools/' . DB_NAME . '.db';
            }

            $conn = new PDO($dsnWithDb, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            Response::output('Database selection error: ' . $e->getMessage(), 400);
        }

        // Read and execute queries from the SQL file to create tables. We have a different migrate file for different database drivers
        $migrateFile = ROOT . '/.tools/migrate_' . DB_DRIVER . '.sql';
        $migrate = file_get_contents($migrateFile);

        try {
            // Execute multiple queries
            $conn->exec($migrate);
        } catch (PDOException $e) {
            Response::output('Error in migrate file: ' . $e->getMessage(), 400);
        }

        $ip = IP::currentIP();

        // Insert CSP approved domain for the current host
        $this->createCSPApprovedDomain($conn, $_SERVER['HTTP_HOST']);
        if (IP::isPublicIp($ip)) {
            // Insert firewall rule for the current IP if it's not private IP (because we already insert private ranges by default)
            $this->createFirewallRule($conn, $ip);
        }
        // Insert administrator for first time login but only if local login is used
        if (LOCAL_USER_LOGIN) {
            $password = General::randomString(12);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $countryCode = 'US';
            if (IP::isPublicIp($ip)) {
                $ipGeoLocate = \App\Request\NativeHttp::get('http://ip-api.com/json/' . $ip, [], true);
                if ($ipGeoLocate['status'] === 'success') {
                    $countryCode = $ipGeoLocate['countryCode'];
                }
            }

            $this->createAdminUser($conn, $hashedPassword, $ip, $countryCode);

            // Print the credentials to the screen
            $html .= Alerts::success('Database "' . DB_NAME . '" and system tables created successfully. Please go to <a class="underline" href="/login">Login</a> page. Use "admin" as username. Do not refresh the page until you have copied the password below.<p class="text-black dark:text-gray-400"><strong><span class="c0py">' . $password . '</span></strong></p>');
        } else {
            $html .= Alerts::success('Database "' . DB_NAME . '" and system tables created successfully. Please go to <a class="underline" href="/login">Login</a> page. Because no local login is enabled you need to control admin accounts through the provider claims.');
        }

        return $html;
    }
    public function createAdminUser($conn, $hashedPassword, $ip, $countryCode)
    {
        try {
            $now = (DB_DRIVER === 'sqlite') ? 'CURRENT_TIMESTAMP' : 'NOW()';
            $enabled = (DB_DRIVER === 'mysql') ? 1 : 'TRUE';

            if (DB_DRIVER === 'mysql') {
                $stmt = $conn->prepare("INSERT INTO users (username, password, email, name, last_ips, origin_country, role, last_login, theme, provider, enabled) VALUES ('admin', ?, 'admin', 'admin', ?, ?, 'administrator', $now, ?, 'local', ?);");
                $stmt->execute([$hashedPassword, $ip, $countryCode, COLOR_SCHEME, $enabled]);
            } elseif (DB_DRIVER === 'pgsql' || DB_DRIVER === 'sqlite') {
                $stmt = $conn->prepare("INSERT INTO users (username, password, email, name, last_ips, origin_country, role, last_login, theme, provider, enabled) VALUES ('admin', ?, 'admin', 'admin', ?, ?, 'administrator', $now, ?, 'local', CAST(? AS BOOLEAN));");
                $stmt->execute([$hashedPassword, $ip, $countryCode, COLOR_SCHEME, 1]);
            }
        } catch (PDOException $e) {
            Response::output('Inserting admin user error: ' . $e->getMessage(), 400);
        }
    }
    public function createFirewallRule($conn, $ip)
    {
        try {
            
            $stmt = $conn->prepare("INSERT INTO firewall (ip_cidr, created_by, comment) VALUES (?, 'System', 'Initial Admin IP')");
            $stmt->execute([$ip . '/32']);
        } catch (PDOException $e) {
            Response::output('Inserting firewall rule for IP ' . $ip . ' error: ' . $e->getMessage(), 400);
        }
    }
    public function createCSPApprovedDomain($conn, $domain)
    {
        try {
            $stmt = $conn->prepare("INSERT INTO csp_approved_domains (domain, created_by) VALUES (?, 'System')");
            $stmt->execute([$domain]);
        } catch (PDOException $e) {
            Response::output('Inserting csp_approved_domains rule for domain ' . $domain . ' error: ' . $e->getMessage(), 400);
        }
    }
}
