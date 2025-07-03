<?php
declare(strict_types=1);

namespace App\Hooks;

use PDO;
use PDOException;

class RegisterHook
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function register(string $table, array $data): int
    {
        try {
            // Extraer columnas y placeholders
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_map(fn($key) => ':' . $key, array_keys($data)));

            $sql = "INSERT INTO lavanderia.app.$table ($columns) VALUES ($placeholders)";
            $stmt = $this->conn->prepare($sql);

            // Ejecutar con bind automático
            $stmt->execute($data);

            return (int)$this->conn->lastInsertId();
        } catch(PDOException $e) {
            http_response_code(500);
            die(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));
        }
    }
}
