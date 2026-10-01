<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

const APP_NAME = 'E-Library';
const APP_VERSION = '1.0.0';
const DEFAULT_MAX_UPLOAD_MB = 20;

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$root = preg_replace('#/(admin|librarian|user|api)(?:/.*)?$#', '', $scriptDir);
$root = rtrim((string)$root, '/');
define('BASE_URL', $root === '/' ? '' : $root);
define('APP_ROOT', dirname(__DIR__));
define('UPLOAD_DIR', APP_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'books');
define('UPLOAD_WEB_PATH', 'uploads/books');
define('UPLOAD_COVER_DIR', APP_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'covers');
define('UPLOAD_COVER_WEB_PATH', 'uploads/covers');

// Change these values for your XAMPP or InfinityFree database.
define('DB_HOST', getenv('ELIBRARY_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('ELIBRARY_DB_NAME') ?: 'dlibrary');
define('DB_USER', getenv('ELIBRARY_DB_USER') ?: 'root');
define('DB_PASS', getenv('ELIBRARY_DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');
