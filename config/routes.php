<?php

use App\Controllers\CustomerController;
use App\Controllers\IndexController;
use App\Routes\RouteCollector as Router;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

$router = new Router();

$router->get('/test', [IndexController::class, 'handle']);
$router->post('/test', [CustomerController::class, 'handle']);
$router->get('/test/{id}', function (ServerRequestInterface $request) {
    return new Response(
        status: 201,
        body: $request->getQueryParams()['id']
    );
});

return $router;
