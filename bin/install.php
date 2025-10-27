#!/usr/bin/env php
<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este instalador solo puede ejecutarse desde la línea de comandos.\n");
    exit(1);
}

require_once __DIR__ . '/../app/support/helpers.php';
require_once __DIR__ . '/../app/support/Config.php';
require_once __DIR__ . '/../app/support/Database.php';
require_once __DIR__ . '/../app/support/Installer.php';
require_once __DIR__ . '/../app/Models/Repositories/SettingRepository.php';

use App\Support\Installer;

$basePath = dirname(__DIR__);
$configPath = $basePath . '/config.php';
$config = file_exists($configPath) ? require $configPath : [];

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

fwrite(STDOUT, "\nPaso 1/3: configuración de la base de datos\n");
$defaultDatabase = (string) ($config['database'] ?? 'storage/database.sqlite');
$databaseExpression = '';
$absolute = '';

while (true) {
    $input = prompt_cli("Ruta del archivo SQLite", $defaultDatabase);

    try {
        [$databaseExpression, $absolute] = Installer::determineDatabase($input, $basePath);
        Installer::writeConfig($configPath, $databaseExpression, false);
        fwrite(STDOUT, "✔ Ruta guardada en config.php\n\n");
        break;
    } catch (\InvalidArgumentException | \RuntimeException $e) {
        fwrite(STDERR, '⚠ ' . $e->getMessage() . "\n");
    }
}

fwrite(STDOUT, "Paso 2/3: datos generales del sitio\n");

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

fwrite(STDOUT, "\nPaso 3/3: confirmación\n");

fwrite(STDOUT, "Se guardarán los siguientes datos:\n");
fwrite(STDOUT, " - Ruta de la base de datos: {$absolute}\n");
fwrite(STDOUT, " - Nombre del sitio: {$siteName}\n");
fwrite(STDOUT, " - Correo de contacto: " . ($contactEmail !== '' ? $contactEmail : '(vacío)') . "\n");
fwrite(STDOUT, " - Moneda por defecto: {$defaultCurrency}\n");
fwrite(STDOUT, " - Teléfono de soporte: " . ($supportPhone !== '' ? $supportPhone : '(vacío)') . "\n\n");

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
    Installer::writeConfig($configPath, $databaseExpression, true);
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
