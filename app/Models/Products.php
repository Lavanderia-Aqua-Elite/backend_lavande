<?php
declare(strict_types=1);

namespace app\Models;

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
    public function show(): void
    {
        $sql = "SELECT * FROM lavanderia_app.products";
        $stmt = $this->conn->prepare($sql);
        $stmt->fetchAll();
        $stmt->execute();
    }

    #INSERT
    public function register(array $data): int
    {
    
        $sql = "INSERT INTO lavanderia_app.products (nam_p, price_p, units_p, color_p, brand_p, nom_model_p, recommended_use, opinion_clients, size_p, commentary_p)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
        
        http_response_code(201);
        return (int)$this->conn->lastInsertId();
    }

    #UPDATE:
    public function update(int $id, array $data): void
    {
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
            $data["nam_p"] => ":name",
            $data["price_p"] => ":price",
            $data["units_p"] => ":units",
            $data["color_p"] => ":color",
            $data["brand_p"] => ":brand",
            $data["nam_model"] => ":nam_model",
            $data["recommended_use"] => ":recommended",
            $data["opinion_clients"] => ":opinion",
            $data["size_p"] => ":size",
            $data["commentary"] => ":commentary",
            $id => ":id_product"
        ]);
    }

    #DELETE
    public function delete(int $id): void
    {
        $sql = "DELETE FROM lavanderia_app.products WHERE id_product = :id_product";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $id => ":id_product"
        ]);
    }
}