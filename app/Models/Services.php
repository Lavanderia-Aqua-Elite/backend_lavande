<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use App\Hooks\RegisterHook;
use App\Hooks\DeleteHook;
use App\Hooks\UpdateHook;
use App\Hooks\GetAllHook;
use App\Hooks\ImageHook;

class Services
{
    private PDO $conn;
    private RegisterHook $registerHook;
    private DeleteHook $deleteHook;
    private UpdateHook $updateHook;
    private GetAllHook $getAllHook;
    private ImageHook $imageHook;
    
    private static string $table = 'services';
    private static string $primaryKey = 'id_service';

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
        $this->registerHook = new RegisterHook($conn);
        $this->deleteHook = new DeleteHook($conn);
        $this->updateHook = new UpdateHook($conn);
        $this->getAllHook = new GetAllHook($conn);
        $this->imageHook = new ImageHook('uploads/services/');
    }

    # GET ALL SERVICES
    public function getAll(): array
    {
        return $this->getAllHook->getAll(self::$table);
    }

    # INSERT SERVICE
    public function insert(array $data): int
    {
        // Preparar datos básicos
        $serviceData = [
            'name_s' => $data['name_s'],
            'price_s' => $data['price_s'],
            'description_s' => $data['description_s']
        ];

        // Manejar imagen si está presente
        if (!empty($_FILES['image_s'])) {
            $imageName = $this->imageHook->handleUpload($_FILES, 'image_s');
            if ($imageName) {
                $serviceData['image_s'] = $imageName;
            }
        }

        return $this->registerHook->register(self::$table, $serviceData);
    }

    # UPDATE SERVICE
    public function update(int $id, array $data): bool
    {
        // Preparar datos básicos
        $updateData = [
            'name_s' => $data['name_s'],
            'price_s' => $data['price_s'],
            'description_s' => $data['description_s']
        ];

        // Manejar imagen si está presente
        if (!empty($_FILES['image_s'])) {
            $imageName = $this->imageHook->handleUpload($_FILES, 'image_s');
            if ($imageName) {
                $updateData['image_s'] = $imageName;
                
                // Eliminar imagen anterior si existe
                $service = $this->getById($id);
                if ($service && !empty($service['image_s'])) {
                    $oldImagePath = 'uploads/services/' . $service['image_s'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            }
        }

        return $this->updateHook->update(
            self::$table,
            $updateData,
            self::$primaryKey,
            $id
        );
    }

    # DELETE SERVICE
    public function delete(int $id): bool
    {
        // Eliminar imagen asociada si existe
        $service = $this->getById($id);
        if ($service && !empty($service['image_s'])) {
            $imagePath = 'uploads/services/' . $service['image_s'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        return $this->deleteHook->delete(
            self::$table,
            self::$primaryKey,
            $id
        );
    }

    # GET SERVICE BY ID
    public function getById(int $id): ?array
    {
        try {
            $query = "SELECT * FROM lavanderia_app." . self::$table . " WHERE " . self::$primaryKey . " = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            throw new \RuntimeException("Error al obtener servicio: " . $e->getMessage());
        }
    }

    # UPDATE SERVICE IMAGE ONLY
    public function updateImage(int $id, array $fileData): bool
    {
        $imageName = $this->imageHook->handleUpload($fileData, 'image_s');
        if (!$imageName) {
            throw new \RuntimeException('Error al subir la imagen');
        }

        // Eliminar imagen anterior si existe
        $service = $this->getById($id);
        if ($service && !empty($service['image_s'])) {
            $oldImagePath = 'uploads/services/' . $service['image_s'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        return $this->updateHook->update(
            self::$table,
            ['image_s' => $imageName],
            self::$primaryKey,
            $id
        );
    }

    # GET SERVICES WITH FILTERS (OPCIONAL)
    public function getFiltered(array $filters = []): array
    {
        try {
            $query = "SELECT * FROM lavanderia_app." . self::$table . " WHERE 1=1";
            $params = [];

            if (!empty($filters['name'])) {
                $query .= " AND name_s LIKE :name";
                $params[':name'] = '%' . $filters['name'] . '%';
            }

            if (!empty($filters['min_price'])) {
                $query .= " AND price_s >= :min_price";
                $params[':min_price'] = $filters['min_price'];
            }

            if (!empty($filters['max_price'])) {
                $query .= " AND price_s <= :max_price";
                $params[':max_price'] = $filters['max_price'];
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \RuntimeException("Error al obtener servicios filtrados: " . $e->getMessage());
        }
    }
}