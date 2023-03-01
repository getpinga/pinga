<?php

namespace App\Controllers;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexController implements RequestHandlerInterface
{
    public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
    {
        return new Response(
            200,
            [
                'Content-Type'=>'application/json'
            ],
            json_encode([
                'name' => 'Foo'
            ])
        );
    }
}
