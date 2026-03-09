<?php

namespace App\Core;

use App\Models\UserModel;

class Auth
{
    public static function user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        static $user = null;
        if ($user === null || (int)$user['id'] !== (int)$_SESSION['user_id']) {
            $userModel = new UserModel();
            $user = $userModel->findWithRole((int) $_SESSION['user_id']);
        }

        return $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
