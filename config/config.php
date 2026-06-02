<?php

// database settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define("DB_PASS", '');
define('DB_NAME', 'tinyrent');
define('APP_DEBUG', true);

// website settings
define('SITE_NAME', 'Kids car rental');
define('CSS_URL', '/tinyrides/public');

// new section start
$envBase = $_ENV['APP_URL'] ?? getenv('APP_URL');
if ($envBase) {
    define('BASE_URL', rtrim($envBase, '/'));
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    $basePath = ($basePath === '/' ? '' : $basePath);
    define('BASE_URL', $scheme . '://' . $host . $basePath);
}
// new section end

// upload settings
define('UPLOAD_URL', BASE_URL . '/public/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// session settings
ini_set('session.cookie_httponly', 1);
session_start();

// error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', 1);


// pagination & display settings
define('ITEMS_PER_PAGE', 12);
define('HOME_CARS_LIMIT', 12);
define('RECENT_CARS_LIMIT', 12);
define('POPULAR_CARS_LIMIT', 6);
define('HOMEPAGE_CARS_LIMIT', 3);

// environment
$host = $_SERVER['HTTP_HOST'] ?? '';
define(
    'APP_ENV',
    str_starts_with($host, 'localhost') || str_starts_with($host, '127.0.0.1')
        ? 'local'
        : 'production'
);

define('AUTH_DISABLED', APP_ENV === 'local');
