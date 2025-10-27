<?php

declare(strict_types=1);

namespace App\Support;

final class Auth
{
    private const SESSION_KEY = '_auth_user';
    private const INTENDED_KEY = '_intended_path';

    public static function check(): bool
    {
        \session_boot();

        return isset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * @return array{username: string}|null
     */
    public static function user(): ?array
    {
        \session_boot();

        /** @var array{username: string}|null $user */
        $user = $_SESSION[self::SESSION_KEY] ?? null;

        return $user !== null ? ['username' => (string) $user['username']] : null;
    }

    public static function attempt(string $username, string $password): bool
    {
        \session_boot();

        /** @var array<string, mixed> $admin */
        $admin = Config::get('admin', []);
        $expectedUser = (string) ($admin['username'] ?? '');
        $hash = (string) ($admin['password_hash'] ?? '');

        if ($expectedUser === '' || $hash === '') {
            return false;
        }

        if (!hash_equals($expectedUser, $username)) {
            return false;
        }

        if (!password_verify($password, $hash)) {
            return false;
        }

        $_SESSION[self::SESSION_KEY] = ['username' => $expectedUser];
        Csrf::regenerate();

        return true;
    }

    public static function logout(): void
    {
        \session_boot();
        unset($_SESSION[self::SESSION_KEY]);
        Csrf::regenerate();
    }

    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }

        \session_boot();
        $requested = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $_SESSION[self::INTENDED_KEY] = $requested !== '' ? $requested : '/';
        \flash('Debes iniciar sesión para continuar.');
        \redirect('/login');
    }

    public static function consumeIntended(): string
    {
        \session_boot();
        $target = (string) ($_SESSION[self::INTENDED_KEY] ?? '/');
        unset($_SESSION[self::INTENDED_KEY]);

        if ($target === '' || $target === '/login') {
            return '/';
        }

        return $target;
    }
}
