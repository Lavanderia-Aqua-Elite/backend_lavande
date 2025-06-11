<?php
declare(strict_types=1);

namespace App\Controller;

session_start();

require __DIR__ . "/../../vendor/autoload.php";

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Firebase\JWT\JWT;
use Slim\Exception\HttpNotFoundException;

//clases:
use App\Validator\Validator;
use App\Models\Client;
use Slim\Exception\HttpException;

class AuthenticationController
{
    private Client $clientModel;
    private $key;

    public function __construct(Client $clientModel)
    {
        $this->clientModel = $clientModel;
        $this->key = $_ENV["KEY_JWT_SECRET"];
    }

    public function login(Request $request, Response $response, array $data): Response
    {
        try 
        {
            $data = $request->getParsedBody();
            $validate = new Validator();

            if(!empty($validate->validateGump($data))) {
                $response->getBody()->write(json_encode([
                    "message" => "Error en la validacion",
                    "status" => 400
                ]));
                return $response->withHeader("Content-Type", "application/json")->withStatus(400);
            }

            $stmt = $this->clientModel->getById($data["id"]);
            
            if(!password_verify($data["password"], $stmt["password"])) {
                $response->getBody()->write(json_encode([
                    "message" => "Fallo la autenticacion, vuelva a intentarlo",
                    "status" => 400
                ]));

                return $response->withHeader("Content-Type", "application/json")->withStatus(400);
            }

            $_SESSION["rol"] = $stmt["role"];

            $payload = [
                "email" => $data["email"],
                "exp" => (60 * 60 * 24)
            ];

            $jwt = JWT::encode($payload, $this->key, "HS250");
            
            if($_SESSION["rol"] === 1) {
                $response->getBody()->write(json_encode([
                    "message" => "cliente",
                    "token" => $jwt,
                    "status" => 200
                ]));
                return $response->withHeader("Content-Type", "application/json")->withStatus(200);
            }
            elseif($_SESSION["rol"] === 2) {
                $response->getBody()->write(json_encode([
                    "message" => "admin",
                    "token" => $jwt,
                    "status" => 200
                ]));
                return $response->withHeader("Content-Type", "application/json")->withStatus(200);
            }

            $response->getBody()->write(json_encode([
                "token" => $jwt
            ]));
            return $response->withHeader("Content-Type", "application/json");
        } 
        catch(HttpException $e) 
        {
            $response->getBody()->write(json_encode([
                "message" => $e->getMessage(),
                "status"=> 500
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(500);
        }
    }

    public function register(Request $request, Response $response, array $data): Response
    {
        try 
        {
            $data = $request->getParsedBody();

            $validate = new Validator();

            if(!empty($validate->validateGump($data))) {
                $response->getBody()->write(json_encode([
                    "message" => "No pasaron los datos",
                    "status" => 400
                ]));
                return $response->withHeader("Content-Type", "application/json")->withStatus(400);
            }       

            $stmt = $this->clientModel->register($data);

            $response->getBody()->write(json_encode([
                "message" => "cliente registrado",
                "data" => $stmt, 
                "status" => 201
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(201);
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