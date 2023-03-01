<?php

namespace App\Controllers;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CustomerController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        echo PHP_EOL."ParsedBody: ".PHP_EOL;var_dump($request->getParsedBody());echo PHP_EOL;
        echo PHP_EOL."UploadedFiles: ".PHP_EOL;var_dump($request->getUploadedFiles());echo PHP_EOL;
        return new Response(
            status: 201,
            headers: ['Content-type' => 'application/json'],
            body: (string) $request->getBody()
        );
    }
}
