<?php

declare(strict_types=1);

use App\Support\Csrf;

function session_boot(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_secure' => isset($_SERVER['HTTPS']),
        ]);
    }
}

function view(string $template, array $data = []): string
{
    return App\Support\View::render($template, $data);
}

function response(array $payload, int $status = 200): void
{
    App\Support\Response::json($payload, $status);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') . '">';
}

function redirect(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function flash(?string $message = null): ?string
{
    session_boot();

    if ($message !== null) {
        $_SESSION['_flash'] = $message;
        return null;
    }

    $value = $_SESSION['_flash'] ?? null;
    if ($value !== null) {
        unset($_SESSION['_flash']);
    }

    return $value !== null ? (string) $value : null;
}
