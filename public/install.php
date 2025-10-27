<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/support/helpers.php';
require_once __DIR__ . '/../app/support/Config.php';
require_once __DIR__ . '/../app/support/Database.php';
require_once __DIR__ . '/../app/support/Csrf.php';
require_once __DIR__ . '/../app/Models/Repositories/SettingRepository.php';

use App\Models\Repositories\SettingRepository;
use App\Support\Config;
use App\Support\Csrf;
use App\Support\Database;

session_boot();

if (!isset($_SESSION['install']) || !is_array($_SESSION['install'])) {
    $_SESSION['install'] = [];
}

$basePath = dirname(__DIR__);
$configPath = $basePath . '/config.php';
$config = file_exists($configPath) ? require $configPath : [];

if (($config['installed'] ?? false) === true) {
    header('Location: /admin');
    exit;
}

$step = isset($_GET['step']) ? max(1, min(4, (int) $_GET['step'])) : 1;
$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['_token'] ?? null;
    if (!Csrf::validate($token)) {
        $errors[] = 'La sesión expiró, vuelve a intentarlo.';
    } else {
        Csrf::regenerate();

        if ($step === 1) {
            header('Location: ?step=2');
            exit;
        }

        if ($step === 2) {
            $inputPath = trim((string) ($_POST['database'] ?? ''));
            if ($inputPath === '') {
                $errors[] = 'Debes indicar la ruta donde se almacenará la base de datos SQLite.';
            } elseif (str_contains($inputPath, '..')) {
                $errors[] = 'La ruta de la base de datos no puede contener ..';
            } else {
                $isAbsolute = str_starts_with($inputPath, '/') || preg_match('/^[A-Za-z]:\\\\/', $inputPath) === 1;
                if (!$isAbsolute) {
                    $clean = ltrim(str_replace('\\', '/', $inputPath), '/');
                    $expression = "__DIR__ . '/" . addslashes($clean) . "'";
                    $absolute = $basePath . '/' . $clean;
                } else {
                    $absolute = $inputPath;
                    $expression = var_export($absolute, true);
                }

                $directory = dirname($absolute);
                if (!is_dir($directory)) {
                    if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                        $errors[] = 'No se pudo crear el directorio de la base de datos: ' . $directory;
                    }
                }

                if ($errors === []) {
                    write_config($configPath, $expression, false);
                    $_SESSION['install']['database_expression'] = $expression;
                    $_SESSION['install']['database_path'] = $absolute;
                    header('Location: ?step=3');
                    exit;
                }
            }
        }

        if ($step === 3 && $errors === []) {
            $siteName = trim((string) ($_POST['site_name'] ?? ''));
            $contactEmail = trim((string) ($_POST['contact_email'] ?? ''));
            $defaultCurrency = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', (string) ($_POST['default_currency'] ?? '')), 0, 3));
            $supportPhone = trim((string) ($_POST['support_phone'] ?? ''));

            $_SESSION['install']['site_name'] = $siteName;
            $_SESSION['install']['contact_email'] = $contactEmail;
            $_SESSION['install']['default_currency'] = $defaultCurrency ?: 'EUR';
            $_SESSION['install']['support_phone'] = $supportPhone;

            if ($siteName === '') {
                $errors[] = 'El nombre del sitio es obligatorio.';
            }
            if ($contactEmail !== '' && filter_var($contactEmail, FILTER_VALIDATE_EMAIL) === false) {
                $errors[] = 'El correo de contacto no es válido.';
            }
            if ($defaultCurrency === '') {
                $defaultCurrency = 'EUR';
            }

            if ($errors === []) {
                try {
                    Config::load($configPath);
                    Database::migrate();
                    $settings = new SettingRepository();
                    $settings->updateMany([
                        'site_name' => $siteName,
                        'contact_email' => $contactEmail,
                        'default_currency' => $defaultCurrency,
                        'support_phone' => $supportPhone,
                    ]);
                    $expression = $_SESSION['install']['database_expression'] ?? var_export(Config::get('database'), true);
                    write_config($configPath, $expression, true);
                    unset($_SESSION['install']['database_expression'], $_SESSION['install']['database_path']);
                    $_SESSION['install']['complete'] = true;
                    header('Location: ?step=4');
                    exit;
                } catch (\Throwable $e) {
                    $errors[] = 'Error al preparar la base de datos: ' . $e->getMessage();
                }
            }
        }
    }
}

function requirement_status(): array
{
    return [
        'PHP 8.1 o superior' => version_compare(PHP_VERSION, '8.1.0', '>='),
        'Extensión PDO Sqlite' => extension_loaded('pdo_sqlite'),
        'Extensión SimpleXML' => extension_loaded('SimpleXML'),
        'Extensión DOM' => extension_loaded('dom'),
        'Permisos de escritura en config.php' => is_writable(__DIR__ . '/../config.php'),
    ];
}

function write_config(string $path, string $databaseExpression, bool $installed): void
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

    file_put_contents($path, $contents);
}

function render_header(string $title): void
{
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>';
    echo '<link rel="stylesheet" href="/assets/styles.css">';
    echo '<style>.installer{max-width:780px;margin:3rem auto;padding:2rem;background:#fff;border-radius:12px;box-shadow:0 12px 24px rgba(15,23,42,0.12);} .installer h1{margin-top:0;} .installer ol{padding-left:1.4rem;} .status-list{list-style:none;padding:0;} .status-list li{display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid #e5e7eb;} .status-ok{color:#16a34a;} .status-fail{color:#dc2626;} .actions{margin-top:1.5rem;display:flex;gap:1rem;} .muted{color:#6b7280;font-size:.875rem;} textarea{width:100%;}</style>';
    echo '</head><body><div class="installer">';
}

function render_footer(): void
{
    echo '</div></body></html>';
}

render_header('Asistente de instalación');

if ($errors !== []) {
    echo '<div class="alert alert-error">' . htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8') . '</div>';
}

echo '<p class="muted">Paso ' . $step . ' de 4</p>';

if ($step === 1) {
    echo '<h1>Bienvenido</h1>';
    echo '<p>Este asistente configurará la base de datos, los datos básicos del sitio y verificará los requisitos para operar la plataforma inmobiliaria.</p>';
    echo '<ul class="status-list">';
    foreach (requirement_status() as $label => $ok) {
        $class = $ok ? 'status-ok' : 'status-fail';
        $symbol = $ok ? '✔' : '✖';
        echo '<li><span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span><span class="' . $class . '">' . $symbol . '</span></li>';
    }
    echo '</ul>';
    echo '<form method="post"><input type="hidden" name="_token" value="' . htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') . '"><div class="actions">';
    echo '<button class="button" type="submit">Comenzar instalación</button>';
    echo '</div></form>';
} elseif ($step === 2) {
    $savedPath = $_SESSION['install']['database_path'] ?? ($config['database'] ?? ($basePath . '/storage/database.sqlite'));
    echo '<h1>Ruta de la base de datos</h1>';
    echo '<p>Indica la ubicación donde se almacenará el archivo SQLite. Puedes usar una ruta absoluta o relativa a la carpeta del proyecto.</p>';
    echo '<form method="post"><input type="hidden" name="_token" value="' . htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') . '">';
    echo '<label>Ruta del archivo<input type="text" name="database" value="' . htmlspecialchars((string) $savedPath, ENT_QUOTES, 'UTF-8') . '" required></label>';
    echo '<div class="actions"><button class="button" type="submit">Guardar y continuar</button><a class="button-secondary" href="?step=1">Volver</a></div>';
    echo '</form>';
} elseif ($step === 3) {
    echo '<h1>Datos del sitio</h1>';
    echo '<p>Personaliza la información base que se mostrará en la interfaz y en los feeds generados.</p>';
    echo '<form method="post"><input type="hidden" name="_token" value="' . htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') . '">';
    echo '<div class="grid">';
    echo '<label>Nombre del sitio<input type="text" name="site_name" value="' . htmlspecialchars((string) ($_SESSION['install']['site_name'] ?? 'Inmobiliaria Segura'), ENT_QUOTES, 'UTF-8') . '" required></label>';
    echo '<label>Correo de contacto<input type="email" name="contact_email" value="' . htmlspecialchars((string) ($_SESSION['install']['contact_email'] ?? ''), ENT_QUOTES, 'UTF-8') . '"></label>';
    echo '<label>Moneda por defecto<input type="text" name="default_currency" maxlength="3" value="' . htmlspecialchars((string) ($_SESSION['install']['default_currency'] ?? 'EUR'), ENT_QUOTES, 'UTF-8') . '"></label>';
    echo '<label>Teléfono de soporte<input type="text" name="support_phone" value="' . htmlspecialchars((string) ($_SESSION['install']['support_phone'] ?? ''), ENT_QUOTES, 'UTF-8') . '"></label>';
    echo '</div>';
    echo '<div class="actions"><button class="button" type="submit">Finalizar instalación</button><a class="button-secondary" href="?step=2">Volver</a></div>';
    echo '</form>';
} else {
    $complete = !empty($_SESSION['install']['complete']);
    echo '<h1>Instalación completada</h1>';
    if ($complete) {
        unset($_SESSION['install']);
        echo '<p>Todo está listo. Ya puedes acceder al panel administrativo para importar propiedades y gestionar tus portales.</p>';
        echo '<div class="actions"><a class="button" href="/admin">Ir al panel de administración</a><a class="button-secondary" href="/">Ir al panel principal</a></div>';
    } else {
        echo '<p>Los pasos previos no se completaron correctamente. Regresa al inicio del asistente.</p>';
        echo '<div class="actions"><a class="button" href="?step=1">Reintentar</a></div>';
    }
    echo '<p class="muted">Por seguridad, elimina o restringe el acceso a este instalador una vez en producción.</p>';
}

render_footer();
