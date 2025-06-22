<?php
declare(strict_types=1);

namespace app\Models;

//Slim:

use DI\NotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\NotFoundExceptionInterface;

//PDO:
use PDO;

class Products
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    #GET ALL
    public function show(Response $response): Response
    {
        try {
            $sql = "SELECT * FROM lavanderia_app.products";
            $stmt = $this->conn->prepare($sql);
            $stmt->fetchAll();
            $response->getBody()->write(json_encode([
                "items" => $stmt,
                "status" => 200
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(200);
        }
        catch(NotFoundException $e) {
            $response->getBody()->write(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));

            return $response->withHeader("Content-Type", "application")->withStatus(500);
        }
    }

    #INSERT
    public function register(Response $response, array $data): Response
    {
        if(
               !empty($data["nam_p"]) 
            || !empty($data["price_p"]) 
            || !empty($data["units_p"]) 
            || !empty($data["size_p"]) 
            || !empty($data["color_p"]) 
            || !empty($data["brand_p"])
            || !empty($data["nom_model_p"])
            || !empty($data["recommended_use"])
            || !empty($data["opinion_clients"])
            || !empty($data["commentary_p"])
        ) {
            $response->getBody()->write(json_encode([
                "message" => "Los datos no pasaron",
                "status" => 400
            ]));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }

        try 
        {
            $sql = "INSERT INTO lavanderia_app.products (nam_p, price_p, units_p, color_p, brand_p, nom_model_p, recommended_use, opinion_clients, size_p, commentary_p)
                VALUES (:nam, :preice, :units, :color, :brand, :nam_model, :recommended, :opinion, :size, :commentary)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $data["nam_p"],
                $data["price_p"],
                $data["units_p"],
                $data["color_p"],
                $data["brand_p"],
                $data["nam_model"],
                $data["recommended_use"],
                $data["opinion_clients"],
                $data["size_p"],
                $data["commentary"]
            ]);

            $response->getBody()->write(json_encode([
                "message" => "Product regiter successfully",
                "status" => 201
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(201);

        } 
        catch(NotFoundExceptionInterface $e) 
        {
            $response->getBody()->write(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));

            return $response->withHeader("Content-Type", "application")->withStatus(500);
        }
    }

    #UPDATE:
    public function update(Response $response, int $id, array $data): Response
    {
        if(
               !empty($data["nam_p"]) 
            || !empty($data["price_p"]) 
            || !empty($data["units_p"]) 
            || !empty($data["size_p"]) 
            || !empty($data["color_p"]) 
            || !empty($data["brand_p"])
            || !empty($data["nom_model_p"])
            || !empty($data["recommended_use"])
            || !empty($data["opinion_clients"])
            || !empty($data["commentary_p"])
        ) {
            $response->getBody()->write(json_encode([
                "message" => "Los datos no pasaron",
                "status" => 400
            ]));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }

        if(!empty($id)) {
            $response->getBody()->write(json_encode([
                "message" => "El ID no esta pasando",
                "status" => 400
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }

        try {
            $sql = "UPDATE nam_p = :name, 
            price_p = :price, 
            units_p = :units, 
            color_p = :color, 
            brand_p = :brand, 
            nam_model_p = :nam_model, 
            recommended_use = :recommended, 
            opinion_clients = :opinion, 
            size_p = :size, 
            commentary_p = :commentary
            WHERE id_product = :id_product";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $data["nam_p"],
                $data["price_p"],
                $data["units_p"],
                $data["color_p"],
                $data["brand_p"],
                $data["nam_model"],
                $data["recommended_use"],
                $data["opinion_clients"],
                $data["size_p"],
                $data["commentary"].
                $id
            ]);
            $response->getBody()->write(json_encode([
                "message" => "Updated product",
                "status" => 200
            ]));
            return $response->withHeader("Content-Type", "application")->withStatus(200);
        } 
        catch(NotFoundException $e) {
            $response->getBody()->write(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));
            return $response->withHeader("Content-Type", "appliaction")->withStatus(500);
        }
    }

    public function delete(Response $response, int $id): Response
    {
        if(!empty($id)) {
            $response->getBody()->write(json_encode([
                "message" => "El ID no pasa",
                "status" => 400
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(400); 
        }

        try {
            $sql = "DELETE FROM lavanderia_app.products WHERE id_product = :id_product";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $id
            ]);
            $response->getBody()->write(json_encode([
                "message" => "Producto eliminado",
                "status" => 200
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(200);
        }
        catch(NotFoundException $e) {
            $response->getBody()->write(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(500);
        }
    }
}