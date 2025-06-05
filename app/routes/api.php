<?php


//Slim
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';

/**
 * Instantiate App
 *
 * In order for the factory to work you need to ensure you have installed
 * a supported PSR-7 implementation of your choice e.g.: Slim PSR-7 and a supported
 * ServerRequest creator (included with Slim PSR-7)
 */
$app = AppFactory::create();

/**
  * The routing middleware should be added earlier than the ErrorMiddleware
  * Otherwise exceptions thrown from it will not be handled by the middleware
  */
$app->addRoutingMiddleware();

/**
 * Add Error Middleware
 *
 * @param bool                  $displayErrorDetails -> Should be set to false in production
 * @param bool                  $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool                  $logErrorDetails -> Display error details in error log
 * @param LoggerInterface|null  $logger -> Optional PSR-3 Logger  
 *
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Set the Not Found Handler
$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function (Request $request, Throwable $exception, bool $displayErrorDetails) {
        $response = new Response();
        $response->getBody()->write('404 NOT FOUND');

        return $response->withStatus(404);
    });

// Set the Not Allowed Handler
$errorMiddleware->setErrorHandler(
    HttpMethodNotAllowedException::class,
    function (Request $request, Throwable $exception, bool $displayErrorDetails) {
        $response = new Response();
        $response->getBody()->write('405 NOT ALLOWED');

        return $response->withStatus(405);
    });

$app->post('/register', function (Request $request, Response $response, array $args) {
    $response->getBody()->write('register');
    return $response;
});

$app->run();