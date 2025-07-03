<?php
declare(strict_types=1);

namespace App\Api\Paypal;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use GuzzleHttp\Client;

class PaypalAPI
{
    private Client $httpClient;
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->clientId = $_ENV['KEY_PAYPAL_CLIENT_ID'];
        $this->clientSecret = $_ENV['KEY_PAYPAL_CLIENT_SECRET'];
        $this->baseUrl = $_ENV['API_URL_PAYPAL'];
    }

    public function createOrder(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            // 1. Obtener access token
            $response = $this->httpClient->post("{$this->baseUrl}/v1/oauth2/token", [
                'auth' => [$this->clientId, $this->clientSecret],
                'form_params' => ['grant_type' => 'client_credentials']
            ]);
            
            $authData = json_decode($response->getBody(), true);
            $accessToken = $authData['access_token'];
            
            // 2. Crear orden
            $orderResponse = $this->httpClient->post("{$this->baseUrl}/v2/checkout/orders", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$accessToken}"
                ],
                'json' => [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => $data['amount']
                        ]
                    ]],
                    'application_context' => [
                        'return_url' => $data['return_url'],
                        'cancel_url' => $data['cancel_url']
                    ]
                ]
            ]);
            
            $orderData = json_decode($orderResponse->getBody(), true);
            
            return $this->jsonResponse($response, [
                'success' => true,
                'order_id' => $orderData['id'],
                'links' => $orderData['links']
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}