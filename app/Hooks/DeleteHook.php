<?php
declare(strict_types=1);

namespace App\Hooks;

use PDO;
use PDOException;

class DeleteHook
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function delete(string $table, string $column, mixed $value): bool
    {
        try {
            $sql = "DELETE FROM lavanderia.app.$table WHERE $column = :value";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute(['value' => $value]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));
        }
    }
}
