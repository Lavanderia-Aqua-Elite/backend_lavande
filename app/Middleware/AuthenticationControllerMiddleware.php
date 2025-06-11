<?php
declare(strict_types=1);

namespace App\Middleware;

require __DIR__ . "/../../vendor/autoload.php";

use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

class AuthenticationControllerMiddleware implements Middleware
{
    private string $key;

    public function __construct()
    {
        $this->key = $_ENV["KEY_JWT_SECRET"];
    }

    public function process(Request $request, Handler $handler): Response
    {
        $keyJWT = $request->getHeaderLine("Authorization");

        if($keyJWT !== $this->key) {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode([
                "message" => "Unuauthorized",
                "status" => 401
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(401);
        }

        return $handler->handle($request);
    }
}