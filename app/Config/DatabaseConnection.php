<?php
declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class DatabaseConnection
{
    private static ?DatabaseConnection $instance = null;
    private PDO $connection;
    private string $host;
    private string $db;
    private string $user;
    private string $password;
    
    private function __construct() {
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";
            
            $this->connection = new PDO($dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new \RuntimeException("Database connection error");
        }
    }

    public static function getInstance($container): DatabaseConnection {
        if (self::$instance === null) {
            self::$instance = new self($container);
        }
        
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    // Bloqueamos la clonación y deserialización
    public function __clone() {
        throw new \RuntimeException("Cannot clone a singleton");
    }

    public function __wakeup() {
        throw new \RuntimeException("Cannot unserialize a singleton");
    }
}