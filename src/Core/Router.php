<?php
declare(strict_types=1);

namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middleware = []): void {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): void {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middleware): void {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): void {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Витягуємо іменовані параметри
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }

                // Виконуємо middleware
                foreach ($route['middleware'] as $mw) {
                    $this->runMiddleware($mw);
                }

                // Викликаємо handler
                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func_array($handler, [$request, $params]);
                    return;
                }

                if (is_array($handler)) {
                    [$controllerClass, $action] = $handler;
                    if (!class_exists($controllerClass)) {
                        throw new \RuntimeException("Контролер [{$controllerClass}] не знайдено");
                    }
                    $controller = new $controllerClass();
                    if (!method_exists($controller, $action)) {
                        throw new \RuntimeException("Метод [{$action}] у [{$controllerClass}] не знайдено");
                    }
                    call_user_func_array([$controller, $action], [$request, $params]);
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        if (str_starts_with($uri, '/api')) {
            Response::error('Маршрут API не знайдено (404)', 404);
        } else {
            View::render('layouts/404', ['title' => 'Сторінку не знайдено'], null);
        }
    }

    private function runMiddleware(string $middleware): void {
        switch ($middleware) {
            case 'auth':
                if (!Auth::check()) {
                    Response::redirect('/login');
                }
                break;
            case 'admin':
                if (!Auth::check() || !Auth::isAdmin()) {
                    Response::redirect('/dashboard');
                }
                break;
            case 'accountant':
                if (!Auth::check() || !Auth::isAccountant()) {
                    Response::redirect('/dashboard');
                }
                break;
            case 'master':
                if (!Auth::check() || !Auth::isMaster()) {
                    Response::redirect('/dashboard');
                }
                break;
            case 'guest':
                if (Auth::check()) {
                    Response::redirect('/dashboard');
                }
                break;
        }
    }
}
