<?php
declare(strict_types=1);

namespace app\Models;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;

class Sales
{
    private PDO $conn;
    private static $table = "sales";

    public function __construct(PDO $conn) 
    {
        $this->conn = $conn;
    }

    public function register(array $data): string
    {
        if(
            !empty($data[""])
            || !empty($data[""])
        ) {
            http_response_code(400);
            return json_encode([
                "message" => "Los parametros no pasan",
                "status" => 400
            ]);
        }

        $sql = "INSERT INTO lavanderia_app." . self::$table . " () VALUES()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data[""]
        ]);
        return $this->conn->lastInsertId();
    }
}