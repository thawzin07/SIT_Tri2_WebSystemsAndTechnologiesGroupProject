<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;

// Let the built-in server return existing static files directly.
if ($path !== '/' && is_file($file)) {
    return false;
}

require_once __DIR__ . '/index.php';
