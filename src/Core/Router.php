<?php
declare(strict_types=1);

namespace HelpDesk\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string|callable $handler): void
    {
        $this->routes[] = ['GET', $path, $handler];
    }

    public function post(string $path, string|callable $handler): void
    {
        $this->routes[] = ['POST', $path, $handler];
    }

    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if ($routeMethod !== $method) continue;

            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if (is_callable($handler)) {
                    call_user_func($handler, $params);
                    return;
                }

                if (is_string($handler) && str_contains($handler, '@')) {
                    [$class, $action] = explode('@', $handler);
                    $fullClass = "HelpDesk\\Controllers\\{$class}";
                    $controller = new $fullClass();
                    $controller->$action($params);
                    return;
                }
            }
        }

        http_response_code(404);
        echo "<h1 style='font-family:sans-serif;text-align:center;margin-top:100px;'>404 - Chamado ou Página não encontrada</h1>";
    }
}
