<?php

namespace App\Routes;

use App\Controllers\IndexController;
use Nyholm\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router
{
    private ?ContainerInterface $container = null;
    private ?RouteCollector $routeCollector = null;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setRouteCollector(RouteCollector $routeCollector) {
        $this->routeCollector = $routeCollector;
    }

    private function callFunction(array | callable $callable, ServerRequestInterface $serverRequest): ?ResponseInterface
    {
        if (is_array($callable)) {
            if (count($callable) !== 2) {
                throw new \RuntimeException("Route Callable must have 2 elements: 0 = ClassName; 1 = MethodName");
            }
            $method = $callable[1];
            if (isset($this->container) && $this->container->has($callable[0])) {
                return $this->container->get($callable[0])->$method($serverRequest);
            } else {
                $object = new $callable[0]();
                return $object->$method($serverRequest);
            }
        } elseif (is_callable($callable)) {
            return call_user_func($callable, $serverRequest);
        }
    }

    public function execute(ServerRequestInterface $serverRequest): ?ResponseInterface
    {
        $found = false;
        $uri = $serverRequest->getUri();
        $method = strtolower($serverRequest->getMethod());
        $routes = $this->routeCollector->getRoutes();

        foreach ($routes as $route) {
            $pattern = preg_replace('(\{[a-z0-9]{1,}\})', '([a-z0-9]{1,})', $route['path']);
            // find Route
            if (preg_match('#^('.$pattern.')*$#i', $uri, $matches) === 1) {
                if (! in_array($method, $route['methods'])) {
                    $found = true;
                    continue;
                }
                array_shift($matches);
                array_shift($matches);

                $itens = [];
                if (preg_match_all('(\{[a-z0-9]{1,}\})', $route['path'], $m)) {
                    //Remove {}
                    $itens = preg_replace('(\{|\})', '', $m[0]);
                }

                //associate
                $args = [];
                foreach ($matches as $key => $match) {
                    $args[$itens[$key]] = $match;
                }

                $queryParams = $serverRequest->getQueryParams() + $args;
                $serverRequest = $serverRequest->withQueryParams($queryParams);

                return $this->callFunction($route['callable'], $serverRequest);
            }
        }

        if ($found) {
            return new Response(
                status: 405,
                headers: ['Content-type' => 'application/json'],
                body: json_encode([
                    'status' => 405,
                    'message' => 'Method not allowed'
                ]),
                version: '1.1',
                reason: 'Method not allowed');
        }

        return new Response(
            status: 404,
            headers: ['Content-type' => 'application/json'],
            body: json_encode([
                'status' => 404,
                'message' => 'Not Found'
            ]),
            version: '1.1',
            reason: 'Not Found');
    }
}
