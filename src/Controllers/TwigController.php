<?php

namespace App\Controllers;

use App\TwigWrapper;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TwigController
{
    private TwigWrapper $twig;

    public function __construct()
    {
        $templateDir = __DIR__ . '/../../views';
        $this->twig = new TwigWrapper($templateDir);
    }

    public function index(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
    {
        $responseBody = $this->twig->render('test.twig', ['name' => 'Foo']);

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
