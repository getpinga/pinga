<?php

namespace App\Controllers;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserController
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        echo PHP_EOL."ParsedBody: ".PHP_EOL;var_dump($request->getParsedBody());echo PHP_EOL;
        echo PHP_EOL."UploadedFiles: ".PHP_EOL;var_dump($request->getUploadedFiles());echo PHP_EOL;
		$responseBody = (string) $request->getBody();
		$contentLength = strlen($responseBody);

		return new Response(
			status: 201,
			headers: [
				'Content-Type' => 'application/json',
				'Content-Length' => $contentLength,
				'Date' => gmdate('D, d M Y H:i:s').' GMT',
				'Server' => 'Pinga',
				'Cache-Control' => 'max-age=3600',
				'Access-Control-Allow-Origin' => '*'
			],
			body: $responseBody
		);
    }
	
	
    public function getUser(ServerRequestInterface $request, array $args): ResponseInterface
    {
		$responseBody = json_encode(['user' => $args['id']]);
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
