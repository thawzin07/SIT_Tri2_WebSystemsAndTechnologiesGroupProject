<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$cookieSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $cookieSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

require_once __DIR__ . '/../bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$routes = config('routes');
$matched = null;

foreach ($routes as $route) {
    [$routeMethod, $routePath, $controllerName, $action, $middleware] = $route;
    if ($method === $routeMethod && $uri === $routePath) {
        $matched = $route;
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

[, , $controllerName, $action, $middleware] = $matched;

switch ($middleware) {
    case 'guest':
        if (current_user()) {
            redirect(is_admin() ? '/admin/dashboard' : '/member/dashboard');
        }
        break;
    case 'auth':
        if (!current_user()) {
            flash('error', 'Please log in first.');
            redirect('/login');
        }
        break;
    case 'admin':
        if (!current_user() || !is_admin()) {
            flash('error', 'Admin access required.');
            redirect('/admin/login');
        }
        break;
    case 'member':
        if (!current_user() || !is_member()) {
            flash('error', 'Member access required.');
            redirect('/login');
        }
        break;
}

$controllerClass = 'App\\Controllers\\' . $controllerName;
$controller = new $controllerClass();
$controller->$action();
