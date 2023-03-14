# Cookies

Modify your controllers the following way to get access to cookies.

```php

<?php

namespace App\Controllers;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexController
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        // Get cookie value
        $cookies = $request->getCookieParams();
        $cookieValue = isset($cookies['cookie_name']) ? $cookies['cookie_name'] : 'default_value';

        // Your response body
        $responseBody = json_encode(['name' => 'Foo', 'cookieValue' => $cookieValue]);
        $contentLength = strlen($responseBody);

        // Create a new Response object
        $response = new Response(
            200,
            [
                'Content-Type' => 'application/json',
                'Content-Length' => $contentLength,
                'Date' => gmdate('D, d M Y H:i:s').' GMT',
                'Server' => 'Pinglet',
                'Cache-Control' => 'max-age=3600',
                'Access-Control-Allow-Origin' => '*'
            ],
            $responseBody
        );

        // Set a cookie (conditionally)
        if (/* your condition */) {
            $response = $this->setCookie($response, 'cookie_name', 'cookie_value');
        }

        return $response;
    }

    private function setCookie(ResponseInterface $response, string $name, string $value, int $expire = 3600, string $path = '/'): ResponseInterface
    {
        $cookieHeader = sprintf(
            '%s=%s; Expires=%s; Path=%s',
            $name,
            $value,
            gmdate('D, d M Y H:i:s T', time() + $expire),
            $path
        );

        return $response->withAddedHeader('Set-Cookie', $cookieHeader);
    }
}
```
