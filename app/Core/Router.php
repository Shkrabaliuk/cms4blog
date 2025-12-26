<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Simple Router with support for dynamic routes and middleware
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private string $prefix = '';
    private array $groupMiddlewares = [];

    /**
     * Add GET route
     */
    public function get(string $path, callable|array $handler, array $middlewares = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * Add POST route
     */
    public function post(string $path, callable|array $handler, array $middlewares = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * Add PUT route
     */
    public function put(string $path, callable|array $handler, array $middlewares = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    /**
     * Add DELETE route
     */
    public function delete(string $path, callable|array $handler, array $middlewares = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    /**
     * Add PATCH route
     */
    public function patch(string $path, callable|array $handler, array $middlewares = []): self
    {
        return $this->addRoute('PATCH', $path, $handler, $middlewares);
    }

    /**
     * Group routes with prefix and/or middlewares
     */
    public function group(string $prefix, callable $callback, array $middlewares = []): self
    {
        $previousPrefix = $this->prefix;
        $previousMiddlewares = $this->groupMiddlewares;

        $this->prefix = $previousPrefix . $prefix;
        $this->groupMiddlewares = array_merge($previousMiddlewares, $middlewares);

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->groupMiddlewares = $previousMiddlewares;

        return $this;
    }

    /**
     * Add route to collection
     */
    private function addRoute(string $method, string $path, callable|array $handler, array $middlewares = []): self
    {
        $fullPath = $this->prefix . $path;
        $allMiddlewares = array_merge($this->groupMiddlewares, $middlewares);

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middlewares' => $allMiddlewares,
            'pattern' => $this->buildPattern($fullPath),
        ];

        return $this;
    }

    /**
     * Build regex pattern from route path
     */
    private function buildPattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Dispatch request to appropriate handler
     */
    public function dispatch(string $method, string $uri): void
    {
        // Remove query string
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareInstance = new $middleware();
                    $result = $middlewareInstance->handle();
                    if ($result === false) {
                        return;
                    }
                }

                // Execute handler
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        // No route found - 404
        $this->handleNotFound();
    }

    /**
     * Execute route handler
     */
    private function executeHandler(callable|array $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
        } elseif (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            call_user_func_array([$controller, $method], $params);
        }
    }

    /**
     * Handle 404 Not Found
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        
        $errorTemplate = TEMPLATES_PATH . '/errors/404.php';
        if (file_exists($errorTemplate)) {
            include $errorTemplate;
        } else {
            echo '404 Not Found';
        }
    }
}
