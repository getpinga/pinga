<?php

namespace App\Controllers;

use App\PlatesWrapper;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PlatesController
{
    private PlatesWrapper $plates;

    public function __construct()
    {
        $templateDir = __DIR__ . '/../../views';
        $this->plates = new PlatesWrapper($templateDir);
    }

    public function index(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
    {
        $responseBody = $this->plates->render('test', ['name' => 'Foo']);

        $contentLength = strlen($responseBody);

        return new Response(
            200,
            [
                'Content-Type' => 'text/html',
                'Content-Length' => $contentLength,
                'Date' => gmdate('D, d M Y H:i:s').' GMT',
                'Server' => 'Pinga',
                'Cache-Control' => 'max-age=3600',
                'Access-Control-Allow-Origin' => '*'
            ],
            $responseBody
        );
    }
}
