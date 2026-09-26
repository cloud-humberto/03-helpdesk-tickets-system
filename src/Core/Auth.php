<?php
declare(strict_types=1);

namespace HelpDesk\Core;

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax'
            ]);
        }
    }

    public static function user(): ?array
    {
        self::start();
        return $_SESSION['helpdesk_user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && ($user['role'] === 'admin');
    }

    public static function isClient(): bool
    {
        $user = self::user();
        return $user && ($user['role'] === 'client');
    }

    public static function login(array $user): void
    {
        self::start();
        $_SESSION['helpdesk_user'] = [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role']
        ];
    }

    public static function logout(): void
    {
        self::start();
        unset($_SESSION['helpdesk_user']);
        session_destroy();
    }

    public static function csrfToken(): string
    {
        self::start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf(?string $token): bool
    {
        self::start();
        return $token && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
