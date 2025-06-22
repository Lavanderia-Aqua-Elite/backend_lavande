<?php
declare(strict_types=1);

namespace app\Models;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\NotFoundExceptionInterface;

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

    public function insert(Response $response, array $data): Response
    {
        if(
               !empty($data["name_s"])
            || !empty($data["price_s"])
            || !empty($data["description_s"])
        ) {
            $response->getBody()->write(json_encode([
                "message" => "Parameters are not passing",
                "status" => 400
            ]));
            return $response->withHeader("Content-Type", "appliaction/json")->withStatus(400);
        }
        
        try {
            $sql = "INSERT INTO lavanderia_app." . self::$table . " (name_s, price_s, image_s, description_s)
                VALUES (:name, :price, :image, :description)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $data["name_s"],
                $data["price_s"],
                $data["description_s"]
            ]);
            $response->getBody()->write(json_encode([
                "message" => "successfully registered service",
                "status" => 201
            ]));
            return $response->withHeader("Content-Type", "appliaction/json")->withStatus(201);
        }
        catch(NotFoundExceptionInterface $e) {
            $response->getBody()->write(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));
            return $response->withHeader("Content-Type", "appliaction/json")->withStatus(500);
        }
    }
}