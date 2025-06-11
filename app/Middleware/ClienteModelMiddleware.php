<?php
declare(strict_types=1);

namespace App\Middleware;

require __DIR__ . "/../../vendor/autoload.php";

use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;

class ClienteModelMiddleware implements Middleware
{
    public function process(Request $request, Handler $handler): Response
    {
        $response = $handler->handle($request);
        return $response;
    }
}