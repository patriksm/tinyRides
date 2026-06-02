<?php

declare(strict_types=1);
define('ROOT_PATH', __DIR__);
require_once __DIR__ . '/app/core/Language.php';
require_once ROOT_PATH . '/config/config.php';

// session_start();
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'uz', 'ru', 'lv', 'bn'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$langCode = $_SESSION['lang'] ?? 'en';
$language = new Language($langCode);

function t($key)
{
    global $language;
    return $language->translate($key);
}


// 2) Default APP_DEBUG
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', true);
}

// 3) Error reporting
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('display_startup_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

// 4) Log file
$logDir  = ROOT_PATH . '/storage/logs';
$logFile = $logDir . '/app.log';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0777, true);
}
ini_set('error_log', $logFile);

// 5) Convert PHP errors -> exceptions (Notice/Warning catch)
set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// 6) Global exception handler (RuntimeException 404/500)
set_exception_handler(function (Throwable $e) use ($logFile): void {
    error_log($e->__toString());

    // status code
    $code = (int) $e->getCode();
    if ($code < 400 || $code > 599) {
        $code = 500;
    }
    http_response_code($code);

    if (APP_DEBUG) {
        echo "<h1>Unhandled Exception</h1>";
        echo "<pre style='white-space:pre-wrap; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, \"Liberation Mono\", \"Courier New\", monospace;'>";
        echo htmlspecialchars($e->__toString(), ENT_QUOTES, 'UTF-8');
        echo "</pre>";
        echo "<hr>";
        echo "<p><b>Log file:</b> " . htmlspecialchars($logFile, ENT_QUOTES, 'UTF-8') . "</p>";
        exit;
    }

    // Prod: minimal response
    echo "Something went wrong\nPlease try again later.";
    exit;
});

// 7) Core classlar 
require_once ROOT_PATH . '/app/core/Database.php';
require_once ROOT_PATH . '/app/core/Model.php';
require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/core/Router.php';

$router = new Router();
$router->dispatch();
