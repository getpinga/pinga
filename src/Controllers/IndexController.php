<?php

namespace App\Controllers;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexController
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
	$responseBody = json_encode(['name' => 'Welcome to Pinga']);
	$contentLength = strlen($responseBody);

	return new Response(
		200,
		[
			'Content-Type' => 'application/json',
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
