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

class Products
{
    private PDO $conn;
    private RegisterHook $registerHook;
    private DeleteHook $deleteHook;
    private UpdateHook $updateHook;
    private GetAllHook $getAllHook;
    private ImageHook $imageHook;
    
    private static string $table = 'products';
    private static string $primaryKey = 'id_product';

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
        $this->registerHook = new RegisterHook($conn);
        $this->deleteHook = new DeleteHook($conn);
        $this->updateHook = new UpdateHook($conn);
        $this->getAllHook = new GetAllHook($conn);
        $this->imageHook = new ImageHook('uploads/products/');
    }

    # GET ALL PRODUCTS
    public function show(): array
    {
        return $this->getAllHook->getAll(self::$table);
    }

    # INSERT PRODUCT
    public function register(array $data): int
    {
        // Preparar datos básicos
        $productData = [
            'nam_p' => $data['nam_p'],
            'price_p' => $data['price_p'],
            'units_p' => $data['units_p'],
            'color_p' => $data['color_p'],
            'brand_p' => $data['brand_p'],
            'nam_model_p' => $data['nam_model'],
            'recommended_use' => $data['recommended_use'],
            'opinion_clients' => $data['opinion_clients'],
            'size_p' => $data['size_p'],
            'commentary_p' => $data['commentary']
        ];

        // Manejar imagen si está presente
        if (!empty($_FILES['image'])) {
            $imageName = $this->imageHook->handleUpload($_FILES, 'image');
            if ($imageName) {
                $productData['image'] = $imageName;
            }
        }

        return $this->registerHook->register(self::$table, $productData);
    }

    # UPDATE PRODUCT
    public function update(int $id, array $data): bool
    {
        // Preparar datos básicos
        $updateData = [
            'nam_p' => $data['nam_p'],
            'price_p' => $data['price_p'],
            'units_p' => $data['units_p'],
            'color_p' => $data['color_p'],
            'brand_p' => $data['brand_p'],
            'nam_model_p' => $data['nam_model'],
            'recommended_use' => $data['recommended_use'],
            'opinion_clients' => $data['opinion_clients'],
            'size_p' => $data['size_p'],
            'commentary_p' => $data['commentary']
        ];

        // Manejar imagen si está presente
        if (!empty($_FILES['image'])) {
            $imageName = $this->imageHook->handleUpload($_FILES, 'image');
            if ($imageName) {
                $updateData['image'] = $imageName;
                
                // Eliminar imagen anterior si existe
                $product = $this->getById($id);
                if ($product && !empty($product['image'])) {
                    $oldImagePath = 'uploads/products/' . $product['image'];
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

    # DELETE PRODUCT
    public function delete(int $id): bool
    {
        // Eliminar imagen asociada si existe
        $product = $this->getById($id);
        if ($product && !empty($product['image'])) {
            $imagePath = 'uploads/products/' . $product['image'];
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

    # GET PRODUCT BY ID
    public function getById(int $id): ?array
    {
        try {
            $query = "SELECT * FROM lavanderia_app." . self::$table . " WHERE " . self::$primaryKey . " = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            throw new \RuntimeException("Error al obtener producto: " . $e->getMessage());
        }
    }

    # UPDATE PRODUCT IMAGE ONLY
    public function updateImage(int $id, array $fileData): bool
    {
        $imageName = $this->imageHook->handleUpload($fileData, 'image');
        if (!$imageName) {
            throw new \RuntimeException('Error al subir la imagen');
        }

        // Eliminar imagen anterior si existe
        $product = $this->getById($id);
        if ($product && !empty($product['image'])) {
            $oldImagePath = 'uploads/products/' . $product['image'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        return $this->updateHook->update(
            self::$table,
            ['image' => $imageName],
            self::$primaryKey,
            $id
        );
    }
}