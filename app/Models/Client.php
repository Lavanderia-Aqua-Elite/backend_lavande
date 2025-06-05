<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Client
{
    private PDO $conn;
    private static string $table = 'client';

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getById(int $id): ?array
    {
        $query = "SELECT * FROM lavanderia_app." . self::$table . " WHERE client_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getByEmail(string $email): ?array
    {
        $query = "SELECT * FROM lavanderia_app." . self::$table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAll(): array
    {
        $query = "SELECT * FROM lavanderia_app." . self::$table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function register(array $data): int
    {
        $query = "INSERT INTO lavanderia_app." . self::$table . 
                " (name, lastname, email, role_id, password) VALUES (?, ?, ?, 1, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            $data['name'],
            $data['lastname'],
            $data['email'],
            $data['password'] // Se espera que ya venga hasheado desde el controlador
        ]);
        return (int)$this->conn->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $query = "UPDATE lavanderia_app." . self::$table . 
                " SET name = ?, lastname = ?, email = ? WHERE client_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['name'],
            $data['lastname'],
            $data['email'],
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $query = "DELETE FROM lavanderia_app." . self::$table . " WHERE client_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}