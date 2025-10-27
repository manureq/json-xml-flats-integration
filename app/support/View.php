<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Repositories\SettingRepository;
use App\Support\Auth;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        $viewPath = __DIR__ . '/../Views/' . $template . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View {$template} not found");
        }

        try {
            $settings = (new SettingRepository())->all();
        } catch (\Throwable) {
            $settings = [];
        }

        if (!array_key_exists('settings', $data)) {
            $data['settings'] = $settings;
        }

        if (!array_key_exists('flash', $data)) {
            $data['flash'] = \flash();
        }

        if (!array_key_exists('auth', $data)) {
            $data['auth'] = [
                'check' => Auth::check(),
                'user' => Auth::user(),
            ];
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $viewPath;
        return (string) ob_get_clean();
    }
}
