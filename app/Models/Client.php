<?php
declare(strict_types=1);

namespace App\Models;

require __DIR__ . "/../../vendor/autoload.php";

use PDO;
use PDOException;
use App\Hooks\RegisterHook;
use App\Hooks\DeleteHook;
use App\Hooks\UpdateHook;
use App\Hooks\GetAllHook;
use App\Hooks\ImageHook;

class Client
{
    private PDO $conn;
    private RegisterHook $registerHook;
    private DeleteHook $deleteHook;
    private UpdateHook $updateHook;
    private GetAllHook $getAllHook;
    private ImageHook $imageHook;
    private static string $table = 'users';
    private static string $primaryKey = 'client_id';

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
        $this->registerHook = new RegisterHook($conn);
        $this->deleteHook = new DeleteHook($conn);
        $this->updateHook = new UpdateHook($conn);
        $this->getAllHook = new GetAllHook($conn);
        $this->imageHook = new ImageHook('uploads/clients/');
    }

    public function getById(int $id): ?array
    {
        try {
            $query = "SELECT * FROM lavanderia_app." . self::$table . " WHERE " . self::$primaryKey . " = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            throw new \RuntimeException("Error al obtener cliente: " . $e->getMessage());
        }
    }

    // En App\Models\Client
    public function getByEmail(string $email): ?array
    {
        try {
            $query = "SELECT * FROM lavanderia_app." . self::$table . " WHERE email = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            throw new \RuntimeException("Error al obtener cliente por email: " . $e->getMessage());
        }
    }

    public function show(): array
    {
        return $this->getAllHook->getAll(self::$table);
    }

    public function register(array $data): int
    {
        $registrationData = [
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => 1
        ];

        // Manejar imagen si está presente
        if (!empty($_FILES['image'])) {
            $imageName = $this->imageHook->handleUpload($_FILES, 'image');
            if ($imageName) {
                $registrationData['image'] = $imageName;
            }
        }

        return $this->registerHook->register(self::$table, $registrationData);
    }

    public function update(int $id, array $data): bool
    {
        // Manejar imagen si está presente
        if (!empty($_FILES['image'])) {
            $imageName = $this->imageHook->handleUpload($_FILES, 'image');
            if ($imageName) {
                $data['image'] = $imageName;
            }
        }

        return $this->updateHook->update(
            self::$table,
            $data,
            self::$primaryKey,
            $id
        );
    }

    public function delete(int $id): bool
    {
        // Opcional: eliminar imagen asociada si existe
        $client = $this->getById($id);
        if ($client && !empty($client['image'])) {
            $imagePath = 'uploads/clients/' . $client['image'];
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

    public function updateImage(int $id, array $fileData): bool
    {
        $imageName = $this->imageHook->handleUpload($fileData, 'image');
        if (!$imageName) {
            throw new \RuntimeException('Error al subir la imagen');
        }

        // Eliminar imagen anterior si existe
        $client = $this->getById($id);
        if ($client && !empty($client['image'])) {
            $oldImagePath = 'uploads/clients/' . $client['image'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        return $this->update($id, ['image' => $imageName]);
    }
}