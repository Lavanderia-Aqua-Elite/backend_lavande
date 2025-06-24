<?php
declare(strict_types=1);
namespace app\Controller\Services;

use Psr\Container\ContainerExceptionInterface as Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Request;

//class:
use app\Models\Services;

class ServicesController
{
    private Services $service;

    public function __construct(Services $service)
    {
        $this->service = $service;
    }

    public function register(Request $request, Response $response, array $data): Response
    {
        try {
            $data = $request->getParsedBody();

            if(!empty($data["name"]) || !empty($data["price"]) || !empty($data["description"])) {
                $response->getBody()->write(json_encode([
                    "message" => "Los parametros no pasan",
                    "status" => 400
                ]));
                return $response->withHeader("Content-Type", "application/json")->withStatus(400);
            }

            $this->service->insert($data);

            $response->getBody()->write(json_encode([
                "message" => "Service register successfully",
                "status" => 201  
            ]));    
            return $response->withHeader("Content-Type", "application/json")->withStatus(201);
        } 
        catch(Exception $e) {
            $response->getBody()->write(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(500);
        }
    }
}
