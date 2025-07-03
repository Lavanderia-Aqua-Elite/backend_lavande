<?php
declare(strict_types=1);

namespace App\Middleware\Client;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Slim\Psr7\Response as SlimResponse;

class UserAuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            return $this->jsonResponse(new SlimResponse(), [
                'success' => false,
                'message' => 'Token no proporcionado'
            ], 401);
        }

        try {
            $token = str_replace('Bearer ', '', $authHeader);
            $decoded = JWT::decode($token, new Key($_ENV['KEY_JWT_SECRET'], 'HS256'));
            
            // Añadir datos de usuario a la request
            $request = $request->withAttribute('user_id', $decoded->sub)
                             ->withAttribute('user_role', $decoded->role);
            
            return $handler->handle($request);

        } catch (\Exception $e) {
            return $this->jsonResponse(new SlimResponse(), [
                'success' => false,
                'message' => 'Token inválido: ' . $e->getMessage()
            ], 401);
        }
    }

    private function jsonResponse(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}