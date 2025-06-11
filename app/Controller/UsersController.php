<?
declare(strict_types=1);

namespace App\Controller;

use Slim\Psr7\Response;
use Slim\Psr7\Request;
use Slim\Exception\HttpException;

use App\Models\Client;

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
}