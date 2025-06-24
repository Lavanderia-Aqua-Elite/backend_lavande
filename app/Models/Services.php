<?php
declare(strict_types=1);

namespace app\Models;

//PDO
use PDO;

class Services
{
    private PDO $conn;
    private static string $table = "services";

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function file_image($data): void
    {
        $alloewd_extensions_type = ["pgn", "jpg", "jpge"];
        $route = __DIR__ . "/../Assets/img/";

        foreach($_FILES["userfile"]["error"] as $key => $error) {
            if($error === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES["userfile"]["tmp_name"][$key];
                $name = $_FILES["userfile"]["name"][$key];

                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                    // Validamos si la extensión es permitida
                if (!in_array($extension, $alloewd_extensions_type)) {
                    // Si no es válida, respondemos con error 400 (Bad Request)
                    http_response_code(400);
                    echo json_encode(["message" => "Tipo incorrecto de archivo: $name"]);
                    continue; // saltamos este archivo y seguimos con el siguiente
                }

                // basename elimina cualquier ruta extraña para evitar ataques
                $safe_name = basename($name);

                // Construimos la ruta final donde se guardará el archivo
                $destination = $route . $safe_name;

                // Movemos el archivo desde la carpeta temporal a la carpeta final
                if (!move_uploaded_file($tmp_name, $destination)) {
                    // Si falla al mover, respondemos con error 500 (Internal Server Error)
                    http_response_code(500);
                    echo json_encode(["message" => "Error al guardar el archivo $safe_name"]);
                }

            }
        }
    }

    public function insert(array $data): int
    {
        $sql = "INSERT INTO lavanderia_app." . self::$table . " (name_s, price_s, image_s, description_s)
            VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data["name_s"],
            $data["price_s"],
            $data["description_s"]
        ]);
        return (int)$this->conn->lastInsertId();
    }
}