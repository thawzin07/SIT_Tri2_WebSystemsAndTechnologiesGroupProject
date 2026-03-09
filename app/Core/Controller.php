<?php

namespace App\Core;

class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        view($view, $data, $layout);
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            flash('error', 'Please log in to continue.');
            redirect('/login');
        }
    }

    protected function requireAdmin(): void
    {
        if (!Auth::check() || !is_admin()) {
            flash('error', 'Admin access is required.');
            redirect('/admin/login');
        }
    }

    protected function requireMember(): void
    {
        if (!Auth::check() || !is_member()) {
            flash('error', 'Member access is required.');
            redirect('/login');
        }
    }
}
