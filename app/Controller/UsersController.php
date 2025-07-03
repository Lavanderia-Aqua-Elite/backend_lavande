<?php
declare(strict_types=1);

namespace App\Controller;

use Slim\Psr7\Response;
use Slim\Psr7\Request;
use App\Models\Client;
use App\Validator\Validator;

class UsersController
{
    private Client $clientModel;

    public function __construct(Client $clientModel)
    {
        $this->clientModel = $clientModel;
    }

    /**
     * Obtiene todos los usuarios
     */
    public function getAllUsers(Request $request, Response $response): Response
    {
        try {
            $users = $this->clientModel->show();

            return $this->jsonResponse($response, [
                "data" => $users,
                "count" => count($users),
                "status" => 200
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                "error" => "Error al obtener usuarios",
                "message" => $e->getMessage(),
                "status" => 500
            ], 500);
        }
    }

    /**
     * Actualiza un usuario
     */
    public function updateUser(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int)($args['id'] ?? 0);
            $data = $request->getParsedBody();

            // Validar ID
            if ($id <= 0) {
                return $this->jsonResponse($response, [
                    "error" => "ID inválido",
                    "status" => 400
                ], 400);
            }

            // Validar datos de entrada
            $validator = new Validator();
            $validationRules = [
                'name' => 'required|alpha|max_len,50',
                'lastname' => 'required|alpha|max_len,50',
                'email' => 'required|valid_email'
            ];

            try {
                $validatedData = $validator->validate($data ?? [], $validationRules);
            } catch (\Exception $e) {
                $errors = json_decode($e->getMessage(), true);
                return $this->jsonResponse($response, [
                    "error" => "Validación fallida",
                    "errors" => $errors['validation_errors'] ?? $errors,
                    "status" => 400
                ], 400);
            }

            // Verificar si el usuario existe
            $existingUser = $this->clientModel->getById($id);
            if (!$existingUser) {
                return $this->jsonResponse($response, [
                    "error" => "Usuario no encontrado",
                    "status" => 404
                ], 404);
            }

            // Verificar email único
            if (!empty($validatedData['email'])) {
                $userWithEmail = $this->clientModel->getByEmail($validatedData['email']);
                if ($userWithEmail && $userWithEmail['client_id'] != $id) {
                    return $this->jsonResponse($response, [
                        "error" => "El email ya está en uso",
                        "status" => 400
                    ], 400);
                }
            }

            // Actualizar usuario
            $success = $this->clientModel->update($id, $validatedData);
            
            if (!$success) {
                throw new \Exception("No se pudo actualizar el usuario");
            }

            return $this->jsonResponse($response, [
                "message" => "Usuario actualizado correctamente",
                "status" => 200
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                "error" => "Error al actualizar usuario",
                "message" => $e->getMessage(),
                "status" => 500
            ], 500);
        }
    }

    /**
     * Helper para respuestas JSON consistentes
     */
    private function jsonResponse(Response $response, array $data, int $statusCode = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader("Content-Type", "application/json")
            ->withStatus($statusCode);
    }
}