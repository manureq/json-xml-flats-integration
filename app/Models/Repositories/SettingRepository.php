<?php

declare(strict_types=1);

namespace App\Models\Repositories;

use App\Support\Database;
use PDO;

final class SettingRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        $stmt = $this->db->query('SELECT key, value FROM settings');
        $items = $stmt->fetchAll() ?: [];

        $settings = [];
        foreach ($items as $item) {
            $settings[(string) $item['key']] = (string) $item['value'];
        }

        return $settings;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $stmt = $this->db->prepare('SELECT value FROM settings WHERE key = :key LIMIT 1');
        $stmt->execute([':key' => $key]);
        $value = $stmt->fetchColumn();

        return $value !== false ? (string) $value : $default;
    }

    /**
     * @param array<string, string> $values
     */
    public function updateMany(array $values): void
    {
        $stmt = $this->db->prepare('INSERT INTO settings (key, value) VALUES (:key, :value)
            ON CONFLICT(key) DO UPDATE SET value = excluded.value');

        foreach ($values as $key => $value) {
            $stmt->execute([
                ':key' => $key,
                ':value' => $value,
            ]);
        }
    }
}
