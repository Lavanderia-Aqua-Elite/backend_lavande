<?php
declare(strict_types=1);

namespace App\Hooks;

use PDO;
use PDOException;

class GetAllHook
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getAll(string $table): array
    {
        try {
            $sql = "SELECT * FROM lavanderia.app.$table";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            http_response_code(500);
            return [
                "message" => $e->getMessage(),
                "status" => 500
            ];
        }
    }
}
