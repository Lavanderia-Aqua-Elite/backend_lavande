<?php
declare(strict_types=1);

use App\Models\Client;
use Slim\Factory\AppFactory;
use App\Validator\Validator;
use App\Config\DatabaseConnection;
use App\Controller\AuthenticationController;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$container = new \DI\Container();
$app = AppFactory::create();

$container->set('settings', [
    'displayErrorDetails' => $_ENV[''] === 'true',
    'db' => [
        'host' => $_ENV['KEY_DATA_HOST'],
        'db' => $_ENV['KEY_DATA_DB'],
        'user' => $_ENV['KEY_DATA_USER'],
        'password' => $_ENV['KEY_DATA_PASSWORD']
    ]
]);

$app = AppFactory::setContainer($container);

