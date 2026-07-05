<?php
declare(strict_types=1);

namespace App;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable $handler, bool $requiresAuth = true): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'requiresAuth' => $requiresAuth
        ];
    }

    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                if ($route['requiresAuth'] && empty($_SESSION['user_id'])) {
                    header('Location: /login');
                    exit;
                }
                call_user_func($route['handler']);
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}