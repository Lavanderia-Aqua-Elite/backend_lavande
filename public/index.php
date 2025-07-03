<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

// Create Slim app
$app = AppFactory::create();

// Add middleware for parsing JSON
$app->addBodyParsingMiddleware();

// Add CORS middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Database connection
$db = App\Config\DatabaseConnection::getConnection();

// Routes
$app->group('/api', function (RouteCollectorProxy $group) use ($db) {
    // Authentication
    $group->post('/login', \App\Controller\AuthenticationController::class . ':login');
    $group->post('/register', \App\Controller\AuthenticationController::class . ':register');
    $group->post('/logout', \App\Controller\AuthenticationController::class . ':logout');
    
    // Users
    $group->get('/users', \App\Controller\UsersController::class . ':getAllUsers');
    $group->get('/users/{id}', \App\Controller\UsersController::class . ':getUserById');
    $group->put('/users/{id}', \App\Controller\UsersController::class . ':updateUser');
    
    // Products
    $group->get('/products', \App\Controller\Products\ProductsController::class . ':getAllProducts');
    $group->post('/products', \App\Controller\Products\ProductsController::class . ':createProduct');
    
    // Services
    $group->get('/services', \App\Controller\Services\ServicesController::class . ':getAllServices');
    
    // PayPal
    $group->post('/paypal/create-order', \App\Api\Paypal\PaypalAPI::class . ':createOrder');
});

$app->run();