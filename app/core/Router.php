<?php
class Router
{
    private array $routes;
    private PDO $pdo;

    public function __construct(array $routes, PDO $pdo)
    {
        $this->routes = $routes;
        $this->pdo = $pdo;
    }

    public function dispatch(string $method, string $uri) : void
    {
        foreach ($this->routes as [$routeMethod, $pattern, $controller, $action]) {
            if ($method !== $routeMethod) {
                continue;
            }
            $regex = '#^' . preg_replace('#\{[a-z]+\}#', '([^/]+)', $pattern) . '$#';
            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches);
                $instance = new $controller($this->pdo);
                $instance->$action(...$matches);
                return;
            }
        }

        http_response_code(404);
        require __DIR__ . "/../views/errors/404.php";
    }
}