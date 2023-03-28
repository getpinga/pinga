<?php

use App\Controllers\PlatesController;
use App\Controllers\TwigController;
use App\Controllers\UserController;
use App\Controllers\IndexController;
use FastRoute\RouteCollector;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $r->get('/', [IndexController::class, 'index']);
    $r->get('/user/{id}', [UserController::class, 'getUser']);
    $r->post('/user', [UserController::class, 'index']);
    $r->get('/plates', [PlatesController::class, 'index']);
    $r->get('/twig', [TwigController::class, 'index']);
});

return $dispatcher;
