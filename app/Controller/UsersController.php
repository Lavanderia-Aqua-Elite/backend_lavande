<?
declare(strict_types=1);

namespace App\Controller;

use Slim\Psr7\Response;
use Slim\Psr7\Request;
use Slim\Exception\HttpException;

use App\Models\Client;
use Exception;

class UsersController
{
    private Client $clientModels;

    public function __construct(Client $clientModels)
    {
        $this->clientModels = $clientModels;
    }

    public function show(Request $request, Response $response): Response
    {
        try
        {
            $stmt = $this->clientModels->show();

            $response->getBody()->write(json_encode([
                "items" => $stmt,
                "status" => 200
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(200);
        }
        catch(HttpException $e)
        {
            $response->getBody()->write(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(500);
        }
    }

    public function update(Request $request, Response $response, int $id, array $data): Response
    {
        try 
        {
            $data = $request->getParsedBody();
            $id = $request->getBody();

            if(!empty($data["name"]) || !empty($data["lastname"])) {
                $response->getBody()->write(json_encode([
                    "message" => "Los datos no pasaron",
                    "status" => 400
                ]));
                return $response->withHeader("Content-Type", "application/json")->withStatus(400);
            }

            if(!empty($id)) {
                $response->getBody()->write(json_encode([
                    "message" => "El ID no paso",
                    "status" => 400
                ]));
                return $response->withHeader("Content-Type", "application/json")->withStatus(400);
            }

            $update = $this->clientModels->update($id, $data);

            $response->getBody()->write(json_encode([
                "items" => $update,
                "status" => 200
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(200);
        }
        catch(HttpException $e) {
            $response->getBody()->write(json_encode([
                "message" => $e->getMessage(),
                "status" => 500
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(500);
        }
    }
}