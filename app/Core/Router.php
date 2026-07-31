<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Small dispatcher: maps "METHOD /path" to [ControllerClass, 'method'],
 * supports {param} placeholders and per-route middleware stacks (auth,
 * role checks, guest-only, CSRF). Everything funnels through public/index.php
 * so no individual .php file is directly web-reachable/guessable the way
 * admin.php, status.php, status1.php etc. were in the original project.
 */
final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, array $handler, array $middleware): void
    {
        $this->routes[] = compact('method', 'path', 'handler', 'middleware');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->match($route['path'], $path);
            if ($params === null) {
                continue;
            }

            if ($method === 'POST') {
                Csrf::verifyRequestOrFail();
            }

            foreach ($route['middleware'] as $middlewareClass) {
                /** @var MiddlewareInterface $mw */
                $mw = new $middlewareClass();
                $mw->handle();
            }

            [$controllerClass, $action] = $route['handler'];
            $controller = new $controllerClass();
            echo $controller->$action(...array_values($params));
            return;
        }

        http_response_code(404);
        echo view('errors/404', []);
    }

    private function match(string $routePath, string $requestPath): ?array
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return null;
        }

        $params = [];
        foreach ($routeParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $params[trim($part, '{}')] = $requestParts[$i];
            } elseif ($part !== $requestParts[$i]) {
                return null;
            }
        }

        return $params;
    }
}
