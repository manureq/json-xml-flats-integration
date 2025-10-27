<?php

declare(strict_types=1);

namespace App\Support;

final class Config
{
    private static array $items = [];

    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Config file not found: {$path}");
        }

        /** @var array<string, mixed> $config */
        $config = require $path;
        self::$items = $config;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$items[$key] ?? $default;
    }
}
