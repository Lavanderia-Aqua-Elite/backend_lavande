<?php
declare(strict_types=1);

namespace app\Controller\Products;

use Psr\Container\ContainerExceptionInterface as Exception;
use app\Models\Products;
use PhpParser\Node\Stmt;
use Slim\Psr7\Response;
use Slim\Psr7\Request;

class ProductsController {
    private Products $products;

    public function __construct(Products $products)
    {
        $this->products = $products;
    }

    public function register(Request $request, Response $response, array $data): Response
    {
        try {
            $data = $request->getParsedBody();

            if(!empty($data["name"]) || !empty($data["price"])) {
                $response->getBody()->write(json_encode([
                    "message" => "Los parametros no pasan",
                    "status" => 400
                ]));
                return $response->withHeader("Content-Type", "application/json")->withStatus(400);
            }

            $stmt = $this->products->register($data);

            $response->getBody()->write(json_encode([
                "message" => "Producto/s registrados",
                "data" => $stmt,
                "status" => 201
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(201);
        } catch(Exception $e) {
            $response->getBody()->write(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(500);
        }
    }
}