<?php
//Controller:
use App\Controller\AuthenticationController as Auth;
use App\Controller\UsersController as Users;

//Middleware:
use app\Middleware\AuthenticationControllerMiddleware; #Validar autenticacion del usuario

return function ($app) {
    // POST
    $app->post('/register', Auth::class . ':register');
    $app->post('/login', Auth::class . ':login');
    
    // GET
    $app->group('/', function($group) {
        $group->get('/show', Users::class . ':show');
    });
};