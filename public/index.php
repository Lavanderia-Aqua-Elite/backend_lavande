<?php
declare(strict_types=1);
ini_get('display_errors');

require __DIR__ . '/../vendor/autoload.php';

use App\Models\Client;
use Slim\Factory\AppFactory;
use App\Config\DatabaseConnection;

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Crear contenedor DI
$container = new \DI\Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->setBasePath("/jean_piaget_backend/public");

// Configurar parámetros (como DB y errores)
$container->set('settings', [
    'db' => [
        'host' => $_ENV['KEY_DATA_HOST'],
        'db' => $_ENV['KEY_DATA_DB'],
        'user' => $_ENV['KEY_DATA_USER'],
        'password' => $_ENV['KEY_DATA_PASSWORD'],
    ],
]);

$dbConnection = DatabaseConnection::getInstance($app);
$conn = $dbConnection->getConnection();
$clientModels = new Client($conn);

(require __DIR__ . '/../app/Routes/api.php')($app);

$app->addErrorMiddleware(true, true, true);

$app->run();