<?php

namespace App\Routes;

class RouteCollector
{
    private array $routes = [];

    public function post(string $path, string | array | callable $callable): void
    {
        $this->addRoute(['post'], $path, $callable);
    }

    public function get(string $path, string | array | callable $callable): void
    {
        $this->addRoute(['get'], $path, $callable);
    }

    public function put(string $path, string | array | callable $callable): void
    {
        $this->addRoute(['put'], $path, $callable);
    }

    public function patch(string $path, string | array | callable $callable): void
    {
        $this->addRoute(['patch'], $path, $callable);
    }

    public function delete(string $path, string | array | callable $callable): void
    {
        $this->addRoute(['delete'], $path, $callable);
    }

    public function map(array $methods, string $path, string | array | callable $callable): void
    {
        $methods = array_map('strtolower', $methods);
        $this->addRoute($methods, $path, $callable);
    }

    private function addRoute(array $methods, string $path, string | array | callable $callable): void
    {
        $this->routes[] = [
            'methods' => $methods,
            'path' => $path,
            'callable' => $callable
        ];
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
