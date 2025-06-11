<?php
//Class:
use App\Controller\AuthenticationController;
use App\Controller\UsersController as Users;

return function ($app) {
    // POST
    $app->post('/register', AuthenticationController::class . ':register');
    
    // GET
    $app->get('/show', Users::class . ':show');
};