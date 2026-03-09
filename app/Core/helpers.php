<?php

declare(strict_types=1);

use App\Core\Auth;

function config(string $file): array
{
    static $cache = [];
    if (!isset($cache[$file])) {
        $cache[$file] = require __DIR__ . '/../../config/' . $file . '.php';
    }
    return $cache[$file];
}

function view(string $template, array $data = [], string $layout = 'main'): void
{
    extract($data, EXTR_SKIP);
    $viewPath = __DIR__ . '/../Views/' . $template . '.php';
    $layoutPath = __DIR__ . '/../Views/layouts/' . $layout . '.php';

    if (!file_exists($viewPath) || !file_exists($layoutPath)) {
        http_response_code(500);
        echo 'View rendering error.';
        return;
    }

    ob_start();
    require $viewPath;
    $content = ob_get_clean();

    require $layoutPath;
}

function partial(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require __DIR__ . '/../Views/partials/' . $template . '.php';
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function set_old(array $data): void
{
    $_SESSION['old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function current_user(): ?array
{
    return Auth::user();
}

function is_admin(): bool
{
    $user = Auth::user();
    return $user !== null && ($user['role_name'] ?? '') === 'admin';
}

function is_member(): bool
{
    $user = Auth::user();
    return $user !== null && ($user['role_name'] ?? '') === 'member';
}
