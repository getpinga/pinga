<?php

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;

class PiStaticFileHandler
{
    private $publicPath;

    public function __construct(string $publicPath)
    {
        $this->publicPath = rtrim($publicPath, '/');
    }

    public function handleRequest(ServerRequestInterface $request): ?ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        $file = $this->publicPath . $uri;

        if (file_exists($file) && is_file($file)) {
            $mimeType = mime_content_type($file);
            $stream = Stream::create(fopen($file, 'r'));

            return new Response(
                200,
                [
                    'Content-Type' => $mimeType,
                    'Content-Length' => filesize($file),
                ],
                $stream
            );
        }

        return null;
    }
}
