<?php

declare(strict_types=1);

class Router
{
    private string $controller = 'HomeController';
    private string $method = 'index';
    private array $params = [];

    private array $blockedMethods = [
        'model',
        'view',
        'json',
        'redirect',
        'setMessage',
        'getMessage',
        'requiredLogin',
        'isLoggedIn',
        'csrfToken',
        'verifyCsrf',
        'requireCsrf',
        'post',
        'get',
        'getPost',
        'getGet',
        'e',
    ];

    public function __construct()
    {
        $segments = $this->parseUrl();          // ['auth','logout']
        $segments = array_values($segments);

        // controller
        if (!empty($segments[0])) {
            $seg = $segments[0];
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $seg)) {
                throw new RuntimeException('Not found', 404);
            }
            $this->controller = ucfirst($seg) . 'Controller';
            array_shift($segments);
        }

        // method
        if (!empty($segments[0])) {
            $m = $segments[0];

            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $m)) {
                throw new RuntimeException('Not found', 404);
            }
            if (str_starts_with($m, '__')) {
                throw new RuntimeException('Not found', 404);
            }
            if (in_array($m, $this->blockedMethods, true)) {
                throw new RuntimeException('Not found', 404);
            }

            $this->method = $m;
            array_shift($segments);
        }

        $this->params = $segments;
    }

    public function dispatch(): void
    {
        $controllerFile = ROOT_PATH . '/app/controllers/' . $this->controller . '.php';
        if (!is_file($controllerFile)) {
            throw new RuntimeException('Not found', 404);
        }

        require_once $controllerFile;

        if (!class_exists($this->controller, false)) {
            throw new RuntimeException('Controller class is missing', 500);
        }

        $controllerObj = new $this->controller();

        if (!method_exists($controllerObj, $this->method)) {
            throw new RuntimeException('Not found', 404);
        }

        $ref = new ReflectionMethod($controllerObj, $this->method);
        if (!$ref->isPublic()) {
            throw new RuntimeException('Not found', 404);
        }

        call_user_func_array([$controllerObj, $this->method], $this->params);
    }

    private function parseUrl(): array
    {
        $raw = $_GET['url'] ?? '';
        $raw = urldecode((string)$raw);
        $raw = trim($raw, '/');

        if ($raw === '') return [];

        $parts = explode('/', $raw);
        return array_values(array_filter($parts, static fn($p) => $p !== ''));
    }
}
