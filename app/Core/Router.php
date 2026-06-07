<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<string, array<string, array{0: class-string, 1: string}>> */
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $this->routes[$path][$method] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        // 404 - path does not exist at all
        if (!isset($this->routes[$path])) {
            http_response_code(404);
            view('errors/404', ['view' => 'errors/404']);
            return;
        }

        // 405 - path exists but method not allowed
        if (!isset($this->routes[$path][$method])) {
            http_response_code(405);
            $allowed = implode(', ', array_keys($this->routes[$path]));
            header('Allow: ' . $allowed);
            view('errors/405', ['view' => 'errors/405', 'allowed' => $allowed]);
            return;
        }

        [$class, $action] = $this->routes[$path][$method];
        (new $class())->$action();
    }
}
