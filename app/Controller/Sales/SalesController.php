<?php
declare(strict_types=1);
namespace app\Controller\Sales;

use Psr\Container\ContainerExceptionInterface as Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Request;

//Class:
use app\Models\Sales;

class SalesController
{
    private Sales $sales;

    public function __construct(Sales $sales)
    {
        $this->sales = $sales;
    }

    public function method_of_payment(Request $request, Response $response, array $data): Response
    {
        try {
            
        } catch(Exception $e) {
            $response->getBody()->write(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(500);
        }
    }
}