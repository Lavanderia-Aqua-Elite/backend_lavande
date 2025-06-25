<?php
declare(strict_types=1);

namespace app\Hooks;
use PDOException;
use PDO;

class DeleteHook
{
    private PDO $conn;
    private string $table;
    private array $colums;
    private array $data;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function delete(string $table, array $colums, array $data): void
    {
        try {
            $sql = "DELETE FROM lavanderia_app.". $this->table ." WHERE ". $this->colums ." = ". $this->data;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } catch(PDOException $e) {
            http_response_code(500);
            die(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));
        }
    }
}