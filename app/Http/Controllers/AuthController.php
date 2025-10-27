<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Auth;
use App\Support\Csrf;
use App\Support\View;

final class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            \redirect('/');
        }

        echo View::render('auth/login', [
            'csrf' => Csrf::token(),
        ]);
    }

    public function login(): void
    {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            \flash('Debes indicar usuario y contraseña.');
            \redirect('/login');
        }

        if (!Auth::attempt($username, $password)) {
            \flash('Las credenciales no son válidas.');
            \redirect('/login');
        }

        $target = Auth::consumeIntended();
        \flash('Bienvenido de nuevo.');
        \redirect($target);
    }

    public function logout(): void
    {
        Auth::logout();
        \flash('Sesión finalizada.');
        \redirect('/login');
    }
}
