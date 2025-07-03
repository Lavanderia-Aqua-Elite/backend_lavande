<?php
declare(strict_types=1);

namespace App\Hooks;

use Exception;
use PDO;
use PDOException;

class UpdateHook
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function update(string $table, array $data, string $whereColumn, mixed $whereValue): bool
    {
        try {
            // Eliminar la columna WHERE de los datos a actualizar (por seguridad)
            if (array_key_exists($whereColumn, $data)) {
                unset($data[$whereColumn]);
            }

            // Construir SET dinámicamente
            $setPart = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($data)));

            // Preparar SQL
            $sql = "UPDATE lavanderia.app.$table SET $setPart WHERE $whereColumn = :whereValue";
            $stmt = $this->conn->prepare($sql);

            // Agregar valor del WHERE
            $data['whereValue'] = $whereValue;

            // Ejecutar
            return $stmt->execute($data);

        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));
        }
    }
}
