<?php

use App\Controllers\CustomerController;
use App\Controllers\IndexController;
use FastRoute\RouteCollector;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $r->get('/test', [IndexController::class, 'index']);
    $r->post('/test', [CustomerController::class, 'index']);
    $r->get('/test/{id}', function (ServerRequestInterface $request, array $args) {
        return new Response(
            status: 201,
            body: $args['id']
        );
    });
});

return $dispatcher;
