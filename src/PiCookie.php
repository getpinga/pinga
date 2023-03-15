<?php

namespace App;

use Psr\Http\Message\ResponseInterface;

class PiCookie
{
    public function getCookie(\Psr\Http\Message\ServerRequestInterface $request, string $name, $default = null)
    {
        $cookies = $request->getCookieParams();
		
        return isset($cookies[$name]) ? $cookies[$name] : $default;
    }

    public function setCookie($response, string $name, string $value, int $expire = 3600, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true)
    {
        $cookieHeader = sprintf(
            '%s=%s; Expires=%s; Path=%s; Domain=%s; %s %s',
            $name,
            $value,
            gmdate('D, d M Y H:i:s T', time() + $expire),
            $path,
            $domain,
            $secure ? 'Secure;' : '',
            $httpOnly ? 'HttpOnly' : ''
        );

        return $response->withAddedHeader('Set-Cookie', $cookieHeader);
    }
}
