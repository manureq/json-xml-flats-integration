<?php

declare(strict_types=1);

namespace App\Support;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $path = Config::get('database');
        if (!is_string($path)) {
            throw new \RuntimeException('Database path missing from configuration.');
        }

        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException('Unable to create database directory.');
            }
        }

        try {
            $pdo = new PDO('sqlite:' . $path, options: [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }

        self::$pdo = $pdo;

        return $pdo;
    }

    public static function migrate(): void
    {
        $pdo = self::connection();

        $pdo->exec('CREATE TABLE IF NOT EXISTS properties (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            external_id TEXT UNIQUE,
            title TEXT NOT NULL,
            description TEXT,
            type TEXT,
            address TEXT,
            city TEXT,
            country TEXT,
            latitude REAL,
            longitude REAL,
            rent_price REAL,
            currency TEXT,
            bedrooms INTEGER,
            bathrooms INTEGER,
            area REAL,
            available_from TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS amenities (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            property_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            FOREIGN KEY(property_id) REFERENCES properties(id) ON DELETE CASCADE
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS media (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            property_id INTEGER NOT NULL,
            url TEXT NOT NULL,
            type TEXT NOT NULL,
            caption TEXT,
            FOREIGN KEY(property_id) REFERENCES properties(id) ON DELETE CASCADE
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS portals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            endpoint TEXT,
            auth_token TEXT,
            enabled INTEGER DEFAULT 1
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS property_portal (
            property_id INTEGER NOT NULL,
            portal_id INTEGER NOT NULL,
            PRIMARY KEY (property_id, portal_id),
            FOREIGN KEY(property_id) REFERENCES properties(id) ON DELETE CASCADE,
            FOREIGN KEY(portal_id) REFERENCES portals(id) ON DELETE CASCADE
        )');

        $count = (int) $pdo->query('SELECT COUNT(*) FROM portals')->fetchColumn();
        if ($count === 0) {
            $portals = [
                ['Roomless', 'roomless'],
                ['Spacest', 'spacest'],
                ['Idealista', 'idealista'],
                ['Fotocasa', 'fotocasa'],
                ['Badi', 'badi'],
                ['HousingAnywhere', 'housinganywhere'],
                ['Spotahome', 'spotahome'],
            ];

            $stmt = $pdo->prepare('INSERT INTO portals (name, slug, enabled) VALUES (:name, :slug, 1)');
            foreach ($portals as [$name, $slug]) {
                $stmt->execute([
                    ':name' => $name,
                    ':slug' => $slug,
                ]);
            }
        }

        $defaults = [
            'site_name' => 'Inmobiliaria Segura',
            'contact_email' => 'contacto@example.com',
            'default_currency' => 'EUR',
            'support_phone' => '',
            'feed_note' => '',
        ];

        $stmt = $pdo->prepare('INSERT OR IGNORE INTO settings (key, value) VALUES (:key, :value)');
        foreach ($defaults as $key => $value) {
            $stmt->execute([
                ':key' => $key,
                ':value' => $value,
            ]);
        }
    }
}
