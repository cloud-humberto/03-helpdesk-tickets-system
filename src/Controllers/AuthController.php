<?php
declare(strict_types=1);

namespace HelpDesk\Controllers;

use HelpDesk\Core\Auth;
use HelpDesk\Models\User;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            header('Location: /tickets');
            exit;
        }
        $csrfToken = Auth::csrfToken();
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        $token = $_POST['_csrf_token'] ?? '';
        if (!Auth::validateCsrf($token)) {
            $_SESSION['flash_error'] = 'Invalid CSRF security token.';
            header('Location: /login');
            exit;
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || empty($password)) {
            $_SESSION['flash_error'] = 'Please enter valid credentials.';
            header('Location: /login');
            exit;
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Invalid credentials provided.';
            header('Location: /login');
            exit;
        }

        Auth::login($user);
        $_SESSION['flash_success'] = "Signed in as {$user['name']} ({$user['role']}).";
        header('Location: /tickets');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }
}
