<?php
declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class DatabaseConnection
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    "mysql:host=" . $_ENV['KEY_DATA_HOST'] . ";dbname=" . $_ENV['KEY_DATA_DB'],
                    $_ENV['KEY_DATA_USER'],
                    $_ENV['KEY_DATA_PASSWORD'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                );
            } catch (PDOException $e) {
                throw new PDOException("Connection failed: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}