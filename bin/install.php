#!/usr/bin/env php
<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este instalador solo puede ejecutarse desde la línea de comandos.\n");
    exit(1);
}

$BASE = realpath(__DIR__ . '/..');
if ($BASE === false) {
    fwrite(STDERR, "[ERROR] No se pudo resolver la ruta base del proyecto.\n");
    exit(1);
}

// Resolución robusta del path de Config.php (no depende del cwd)
$CONFIG_FILE = $BASE . '/app/support/Config.php';
if (!is_file($CONFIG_FILE)) {
    fwrite(STDERR, "[ERROR] No se encontró Config.php en: {$CONFIG_FILE}\nEstructura esperada: app/support/Config.php\n");
    exit(1);
}
require_once $CONFIG_FILE;

require_once $BASE . '/app/support/helpers.php';
require_once $BASE . '/app/support/Database.php';
require_once $BASE . '/app/support/Installer.php';
require_once $BASE . '/app/Models/Repositories/SettingRepository.php';

use App\Support\Installer;

$basePath = $BASE;

$storageDir = $basePath . '/storage';
if (!is_dir($storageDir)) {
    if (!@mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
        fwrite(STDERR, "[ERROR] No se pudo crear la carpeta de almacenamiento: {$storageDir}\n");
        exit(1);
    }
}

function ensureWritable(string $path): void
{
    if (is_dir($path)) {
        if (!is_writable($path)) {
            fwrite(STDERR, "[ERROR] La carpeta no es escribible: {$path}\n");
            exit(1);
        }

        return;
    }

    $dir = dirname($path);
    if (!is_dir($dir) || !is_writable($dir)) {
        fwrite(STDERR, "[ERROR] No se puede escribir en el directorio: {$dir}\n");
        exit(1);
    }

    if (file_exists($path) && !is_writable($path)) {
        fwrite(STDERR, "[ERROR] El archivo no es escribible: {$path}\n");
        exit(1);
    }
}

ensureWritable($storageDir);

$configPath = $basePath . '/config.php';
if (!file_exists($configPath) && @touch($configPath) === false) {
    fwrite(STDERR, "[ERROR] No se pudo crear el archivo de configuración en: {$configPath}\n");
    exit(1);
}
ensureWritable($configPath);

$config = [];
if (file_exists($configPath) && filesize($configPath) > 0) {
    $config = require $configPath;
}

$adminConfig = [
    'username' => (string) ($config['admin']['username'] ?? 'admin'),
    'password_hash' => (string) ($config['admin']['password_hash'] ?? ''),
];

$defaultSqlite = $storageDir . '/realestate.sqlite';

if (($config['installed'] ?? false) === true) {
    fwrite(STDOUT, "La plataforma ya está instalada. Puedes acceder al panel con tus credenciales.\n");
    exit(0);
}

fwrite(STDOUT, "Asistente de instalación (CLI)\n===============================\n\n");

$requirements = Installer::requirementStatus($configPath);
$allOk = true;
foreach ($requirements as $label => $ok) {
    fwrite(STDOUT, sprintf("[%s] %s\n", $ok ? 'OK' : 'FALLO', $label));
    if (!$ok) {
        $allOk = false;
    }
}

if (!$allOk) {
    fwrite(STDERR, "\nAlgunos requisitos no se cumplen. Resuélvelos y vuelve a ejecutar el instalador.\n");
    exit(1);
}

fwrite(STDOUT, "\nPaso 1/4: configuración de la base de datos\n");
$defaultDatabase = (string) ($config['database'] ?? $defaultSqlite);
$databaseExpression = '';
$absolute = '';

while (true) {
    $input = prompt_cli("Ruta del archivo SQLite", $defaultDatabase);

    try {
        [$databaseExpression, $absolute] = Installer::determineDatabase($input, $basePath);
        if (!file_exists($absolute) && @touch($absolute) === false) {
            throw new \RuntimeException('No se pudo crear el archivo SQLite en: ' . $absolute);
        }
        Installer::writeConfig($configPath, $databaseExpression, false, $adminConfig);
        fwrite(STDOUT, "✔ Ruta guardada en config.php\n\n");
        break;
    } catch (\InvalidArgumentException | \RuntimeException $e) {
        fwrite(STDERR, '⚠ ' . $e->getMessage() . "\n");
    }
}

fwrite(STDOUT, "Paso 2/4: datos generales del sitio\n");

$siteName = '';
while ($siteName === '') {
    $siteName = prompt_cli('Nombre del sitio', 'Inmobiliaria Segura');
    $siteName = trim($siteName);
    if ($siteName === '') {
        fwrite(STDERR, "⚠ El nombre del sitio es obligatorio.\n");
    }
}

$contactEmail = '';
while (true) {
    $contactEmail = trim(prompt_cli('Correo de contacto (opcional)', ''));
    if ($contactEmail === '' || filter_var($contactEmail, FILTER_VALIDATE_EMAIL) !== false) {
        break;
    }
    fwrite(STDERR, "⚠ El correo no tiene un formato válido.\n");
}

$defaultCurrency = '';
while ($defaultCurrency === '') {
    $currencyInput = strtoupper(preg_replace('/[^A-Z]/', '', prompt_cli('Moneda por defecto (ISO 4217)', 'EUR')));
    if ($currencyInput === '') {
        $defaultCurrency = 'EUR';
        break;
    }

    if (strlen($currencyInput) === 3) {
        $defaultCurrency = $currencyInput;
    } else {
        fwrite(STDERR, "⚠ Debe indicar un código de 3 letras.\n");
    }
}

$supportPhone = trim(prompt_cli('Teléfono de soporte (opcional)', ''));

fwrite(STDOUT, "\nPaso 3/4: credenciales de acceso\n");

$defaultAdminUser = $adminConfig['username'] !== '' ? $adminConfig['username'] : 'admin';
$adminUsername = '';
while ($adminUsername === '') {
    $adminUsername = trim(prompt_cli('Usuario administrador', $defaultAdminUser));
    if ($adminUsername === '') {
        fwrite(STDERR, "⚠ El usuario no puede quedar vacío.\n");
    }
}

$preservePassword = $adminConfig['password_hash'] !== '';
$adminPasswordHash = $adminConfig['password_hash'];

while (true) {
    $hint = $preservePassword ? ' (deja vacío para conservar)' : '';
    $password = prompt_password('Contraseña del administrador' . $hint);

    if ($password === '') {
        if ($preservePassword) {
            break;
        }
        fwrite(STDERR, "⚠ Debes definir una contraseña.\n");
        continue;
    }

    $confirm = prompt_password('Repite la contraseña');
    if ($password !== $confirm) {
        fwrite(STDERR, "⚠ Las contraseñas no coinciden.\n");
        continue;
    }

    $adminPasswordHash = password_hash($password, PASSWORD_DEFAULT);
    break;
}

$adminConfig = [
    'username' => $adminUsername,
    'password_hash' => $adminPasswordHash,
];

fwrite(STDOUT, "\nPaso 4/4: confirmación\n");

fwrite(STDOUT, "Se guardarán los siguientes datos:\n");
fwrite(STDOUT, " - Ruta de la base de datos: {$absolute}\n");
fwrite(STDOUT, " - Nombre del sitio: {$siteName}\n");
fwrite(STDOUT, " - Correo de contacto: " . ($contactEmail !== '' ? $contactEmail : '(vacío)') . "\n");
fwrite(STDOUT, " - Moneda por defecto: {$defaultCurrency}\n");
fwrite(STDOUT, " - Teléfono de soporte: " . ($supportPhone !== '' ? $supportPhone : '(vacío)') . "\n");
fwrite(STDOUT, " - Usuario administrador: {$adminConfig['username']}\n\n");

$confirmation = strtolower(prompt_cli('¿Deseas continuar? (s/n)', 's'));
if (!in_array($confirmation, ['s', 'si', 'sí', 'y', 'yes'], true)) {
    fwrite(STDOUT, "Instalación cancelada. No se realizaron cambios adicionales.\n");
    exit(0);
}

try {
    Installer::finalize($configPath, [
        'site_name' => $siteName,
        'contact_email' => $contactEmail,
        'default_currency' => $defaultCurrency,
        'support_phone' => $supportPhone,
    ]);
    Installer::writeConfig($configPath, $databaseExpression, true, $adminConfig);
} catch (\Throwable $e) {
    fwrite(STDERR, "Ocurrió un error al finalizar la instalación: " . $e->getMessage() . "\n");
    exit(1);
}

fwrite(STDOUT, "\nInstalación completada con éxito.\n");
fwrite(STDOUT, "Publica el contenido de este directorio en tu servidor web preferido y accede al panel administrativo mediante /admin.\n");

/**
 * @param string $question
 * @param string|null $default
 */
function prompt_cli(string $question, ?string $default = null): string
{
    $suffix = $default !== null && $default !== '' ? " [{$default}]" : '';
    fwrite(STDOUT, $question . $suffix . ': ');

    $line = function_exists('readline') ? readline('') : fgets(STDIN);
    if ($line === false) {
        return $default ?? '';
    }

    $line = trim($line);
    if ($line === '' && $default !== null) {
        return $default;
    }

    return $line;
}

function prompt_password(string $question): string
{
    if (stripos(PHP_OS, 'WIN') === 0) {
        fwrite(STDOUT, $question . ': ');
        $line = fgets(STDIN);
        return $line === false ? '' : trim($line);
    }

    $sttyMode = null;
    $sttySupported = false;
    if (function_exists('exec')) {
        $output = [];
        $code = 1;
        @exec('stty -g', $output, $code);
        if ($code === 0 && isset($output[0]) && stripos($output[0], 'inappropriate ioctl') === false) {
            $sttyMode = $output[0];
            $sttySupported = true;
        }
    }

    fwrite(STDOUT, $question . ': ');
    $sttyDisabled = false;
    if ($sttySupported && $sttyMode !== null) {
        $disableStatus = 1;
        @exec('stty -echo 2>/dev/null', $dummy, $disableStatus);
        if ($disableStatus === 0) {
            $sttyDisabled = true;
        } else {
            $sttySupported = false;
        }
    }

    $line = fgets(STDIN);

    if ($sttyDisabled && $sttyMode !== null) {
        @exec('stty ' . $sttyMode . ' 2>/dev/null');
        fwrite(STDOUT, PHP_EOL);
    }

    if ($line === false) {
        return '';
    }

    return trim($line);
}
