<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<int, array{0:string,1:string,2:callable|array}> */
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, callable|array $handler): void
    {
        $this->routes[] = [$method, $path, $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $pattern = preg_replace('/\{[a-zA-Z_]+\}/', '([^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$controller, $action] = $handler;
                call_user_func_array([new $controller(), $action], $matches);
                return;
            }
        }

        http_response_code(404);
        echo 'Page not found';
    }
}
