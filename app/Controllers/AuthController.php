<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\UserModel;

class AuthController extends Controller
{
    public function showRegister(): void
    {
        $this->render('pages/register', ['title' => 'Register']);
    }

    public function register(): void
    {
        verify_csrf();

        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['password_confirmation'] ?? '');

        set_old($_POST);

        if (!Validator::required($fullName) || !Validator::email($email) || !Validator::min($password, 8) || $password !== $confirmPassword) {
            flash('error', 'Please fill in valid registration details.');
            redirect('/register');
        }

        $userModel = new UserModel();
        if ($userModel->findByEmail($email)) {
            flash('error', 'Email is already registered.');
            redirect('/register');
        }

        $userId = $userModel->create([
            'role_id' => 2,
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'phone' => $phone,
        ]);

        $user = $userModel->findWithRole($userId);
        Auth::login($user);
        clear_old();
        flash('success', 'Welcome to PulsePoint Fitness. Your account is ready.');
        redirect('/member/dashboard');
    }

    public function showLogin(): void
    {
        $this->render('pages/login', ['title' => 'Member Login', 'adminMode' => false]);
    }

    public function showAdminLogin(): void
    {
        $this->render('pages/login', ['title' => 'Admin Login', 'adminMode' => true]);
    }

    public function login(): void
    {
        $this->attemptLogin(false);
    }

    public function adminLogin(): void
    {
        $this->attemptLogin(true);
    }

    private function attemptLogin(bool $adminOnly): void
    {
        verify_csrf();

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        set_old($_POST);

        if (!Validator::email($email) || !Validator::required($password)) {
            flash('error', 'Invalid login details.');
            redirect($adminOnly ? '/admin/login' : '/login');
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            flash('error', 'Email or password is incorrect.');
            redirect($adminOnly ? '/admin/login' : '/login');
        }

        $userWithRole = $userModel->findWithRole((int) $user['id']);
        if ($adminOnly && ($userWithRole['role_name'] ?? '') !== 'admin') {
            flash('error', 'Admin credentials are required for this page.');
            redirect('/admin/login');
        }

        Auth::login($userWithRole);
        clear_old();
        flash('success', 'Login successful.');

        if (($userWithRole['role_name'] ?? '') === 'admin') {
            redirect('/admin/dashboard');
        }

        redirect('/member/dashboard');
    }

    public function logout(): void
    {
        verify_csrf();
        Auth::logout();
        session_start();
        flash('success', 'You have been logged out.');
        redirect('/');
    }
}
