<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Repositories\SettingRepository;
use InvalidArgumentException;
use RuntimeException;

final class Installer
{
    /**
     * @return array<string, bool>
     */
    public static function requirementStatus(string $configPath): array
    {
        $storagePath = dirname($configPath) . '/storage';
        $configWritable = file_exists($configPath) ? is_writable($configPath) : is_writable(dirname($configPath));
        $storageWritable = is_dir($storagePath) ? is_writable($storagePath) : is_writable(dirname($storagePath));

        return [
            'PHP 8.1 o superior' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'Extensión PDO Sqlite' => extension_loaded('pdo_sqlite'),
            'Extensión SimpleXML' => extension_loaded('SimpleXML'),
            'Extensión DOM' => extension_loaded('dom'),
            'Permisos de escritura en config.php' => $configWritable,
            'Permisos de escritura en storage/' => $storageWritable,
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function determineDatabase(string $inputPath, string $basePath): array
    {
        $inputPath = trim($inputPath);
        if ($inputPath === '') {
            throw new InvalidArgumentException('Debes indicar la ruta donde se almacenará la base de datos SQLite.');
        }

        if (str_contains($inputPath, '..')) {
            throw new InvalidArgumentException('La ruta de la base de datos no puede contener ..');
        }

        $isAbsolute = str_starts_with($inputPath, '/') || preg_match('/^[A-Za-z]:\\\\/', $inputPath) === 1;
        if (!$isAbsolute) {
            $clean = ltrim(str_replace('\\', '/', $inputPath), '/');
            $expression = "__DIR__ . '/" . addslashes($clean) . "'";
            $absolute = rtrim($basePath, '/\\') . '/' . $clean;
        } else {
            $absolute = $inputPath;
            $expression = var_export($absolute, true);
        }

        $directory = dirname($absolute);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new RuntimeException('No se pudo crear el directorio de la base de datos: ' . $directory);
            }
        }

        return [$expression, $absolute];
    }

    public static function writeConfig(string $path, string $databaseExpression, bool $installed): void
    {
        $flag = $installed ? 'true' : 'false';
        $contents = <<<PHP
<?php

declare(strict_types=1);

return [
    'database' => {$databaseExpression},
    'installed' => {$flag},
];
PHP;

        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException('No se pudo escribir la configuración en ' . $path);
        }
    }

    /**
     * @param array<string, string> $settings
     */
    public static function finalize(string $configPath, array $settings): void
    {
        Config::load($configPath);
        Database::migrate();

        $repository = new SettingRepository();
        $repository->updateMany($settings);
    }
}
