<?php

declare(strict_types=1);
require_once __DIR__ . '/Language.php';

class Controller
{
    protected $userModel;
    protected Language $language;

    public function __construct()
    {
        $langCode = $_SESSION['lang'] ?? 'en';
        $this->language = new Language($langCode);
    }

    private ?Database $db = null;
    private array $models = [];

    // =====================================================
    // Database singleton per request
    // =====================================================
    protected function db(): Database
    {
        if ($this->db === null) {
            $this->db = new Database();
        }
        return $this->db;
    }

    // =====================================================
    // Model loader (with caching + shared DB)
    // =====================================================
    public function model(string $model): object
    {
        $class = ucfirst(trim($model));

        if ($class === '') {
            throw new InvalidArgumentException("Model name cannot be empty");
        }

        // return cached model
        if (isset($this->models[$class])) {
            return $this->models[$class];
        }

        $modelPath = ROOT_PATH . '/app/model/' . $class . '.php';

        if (!file_exists($modelPath)) {
            throw new RuntimeException("Model file not found: $class");
        }

        require_once $modelPath;

        if (!class_exists($class, false)) {
            throw new RuntimeException("Model class not found: $class");
        }

        // inject shared Database
        $instance = new $class($this->db());

        return $this->models[$class] = $instance;
    }

    // =====================================================
    // View loader
    // =====================================================
    public function view(string $view, array $data = []): void
    {
        $view = ltrim($view, '/');

        if ($view === '') {
            throw new InvalidArgumentException("View name cannot be empty");
        }

        $viewPath = ROOT_PATH . '/app/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new RuntimeException("View not found: $view");
        }

        extract($data, EXTR_SKIP);
        $language = $this->language;
        $currentLang = $_SESSION['lang'] ?? 'en';
        require $viewPath;
    }

    // =====================================================
    // JSON response
    // =====================================================
    public function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');

        try {
            echo json_encode(
                $data,
                JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES |
                    JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            http_response_code(500);
            echo '{"error":"Internal server error"}';
        }

        exit();
    }

    // =====================================================
    // Redirect
    // =====================================================
    public function redirect(string $path = ''): void
    {
        $path = trim($path);
        $path = str_replace(["\r", "\n"], '', $path);
        $path = ltrim($path, '/');

        $url = rtrim(BASE_URL, '/') . '/' . $path;

        header("Location: " . $url, true, 302);
        exit();
    }

    // =====================================================
    // Flash messages
    // =====================================================
    public function setMessage(string $type, string $message, array $errors = []): void
    {
        $_SESSION['flash'] = [
            'type'  => $type,
            'text'  => $message,
            'error' => $errors,
        ];
    }

    public function getMessage(): ?array
    {
        if (!isset($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }

    // =====================================================
    // Auth helpers
    // =====================================================
    public function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public function requiredLogin(): void
    {
        if (defined('AUTH_DISABLED') && AUTH_DISABLED) {
            return;
        }

        if (!$this->isLoggedIn()) {
            $this->setMessage('error', 'Please login first');
            $this->redirect('auth/login');
        }
    }

    // =====================================================
    // Input helpers
    // =====================================================
    public function post(string $key, string $default = ''): string
    {
        return isset($_POST[$key])
            ? trim((string)$_POST[$key])
            : $default;
    }

    public function get(string $key, string $default = ''): string
    {
        return isset($_GET[$key])
            ? trim((string)$_GET[$key])
            : $default;
    }

    public function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    // =====================================================
    // CSRF
    // =====================================================
    public function csrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public function verifyCsrf(?string $token): bool
    {
        if (empty($_SESSION['_csrf']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['_csrf'], $token);
    }

    public function requireCsrf(): void
    {
        $token = $_POST['_token'] ?? null;

        if (!$this->verifyCsrf($token)) {
            $this->setMessage('error', 'Invalid request, please try again');
            // $this->redirect('');
        }
    }
}
