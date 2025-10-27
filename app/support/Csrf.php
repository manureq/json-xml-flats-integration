<?php

declare(strict_types=1);

namespace App\Support;

final class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start(['cookie_httponly' => true, 'cookie_secure' => isset($_SERVER['HTTPS'])]);
        }

        if (!isset($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::TOKEN_KEY];
    }

    public static function validate(?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start(['cookie_httponly' => true, 'cookie_secure' => isset($_SERVER['HTTPS'])]);
        }

        return hash_equals($_SESSION[self::TOKEN_KEY] ?? '', (string) $token);
    }

    public static function regenerate(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start(['cookie_httponly' => true, 'cookie_secure' => isset($_SERVER['HTTPS'])]);
        }

        $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        session_regenerate_id(true);
    }
}
