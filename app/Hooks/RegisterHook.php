<?php
declare(strict_types=1);

namespace app\Hooks;

use PDO;
use PDOException;

class RegisterHook
{
    private string $table;
    private array $colums;
    private array $data;
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function register(string $table, array $data, array $colums): int
    {
        try {
            $sql = "INSERT INTO lavanderia.app.". $this->$table . " (". $this->$colums . ")" . " VALUES(". $this->$data . ")";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([

            ]);

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