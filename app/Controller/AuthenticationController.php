<?php
declare(strict_types=1);

namespace App\Controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use App\Models\Client;
use App\Validator\Validator;
use App\Hooks\ImageHook;

class AuthenticationController
{
    private Client $clientModel;
    private Validator $validator;
    private ImageHook $imageHook;
    private string $jwtSecret;

    public function __construct(Client $clientModel, Validator $validator, ImageHook $imageHook)
    {
        $this->clientModel = $clientModel;
        $this->validator = $validator;
        $this->imageHook = $imageHook;
        $this->jwtSecret = $_ENV['KEY_JWT_SECRET'];
    }

    /**
     * Iniciar sesión
     */
    public function login(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            // Validación
            $validation = $this->validator->validate($data, [
                'email' => 'required|valid_email',
                'password' => 'required|min_len,6'
            ]);

            $user = $this->clientModel->getByEmail($data['email']);
            
            if (!$user || !password_verify($data['password'], $user['password'])) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ], 401);
            }

            $token = JWT::encode([
                'sub' => $user['client_id'],
                'email' => $user['email'],
                'role' => $user['role_id'],
                'iat' => time(),
                'exp' => time() + (60 * 60 * 24) // 1 día
            ], $this->jwtSecret, 'HS256');

            return $this->jsonResponse($response, [
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['client_id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role_id'],
                    'image' => $user['image'] ?? null
                ]
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar nuevo usuario
     */
    public function register(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            // Validación
            $validation = $this->validator->validate($data, [
                'name' => 'required|alpha|max_len,50',
                'lastname' => 'required|alpha|max_len,50',
                'email' => 'required|valid_email',
                'password' => 'required|min_len,6'
            ]);

            // Verificar si el email ya existe
            if ($this->clientModel->getByEmail($data['email'])) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'El email ya está registrado'
                ], 400);
            }

            // Procesar imagen si existe
            $files = $request->getUploadedFiles();
            if (!empty($files['image'])) {
                $imageName = $this->imageHook->handleUpload(['image' => $files['image']], 'image');
                if ($imageName) {
                    $data['image'] = $imageName;
                }
            }

            // Hash de la contraseña
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            $data['role_id'] = 1; // Rol de cliente por defecto

            // Registrar usuario
            $userId = $this->clientModel->register($data);

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'user_id' => $userId
            ], 201);

        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request, Response $response): Response
    {
        try {
            // En una API REST, el logout suele ser manejado por el cliente eliminando el token
            // Pero podemos invalidar el token si usamos una lista negra de tokens
            
            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Sesión cerrada exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper para respuestas JSON
     */
    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}