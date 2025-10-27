<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Repositories\PortalRepository;
use App\Models\Repositories\SettingRepository;
use App\Services\ImportService;
use App\Services\UpdateService;
use App\Support\Auth;
use App\Support\View;

final class AdminController
{
    private SettingRepository $settings;
    private PortalRepository $portals;
    private ImportService $importer;
    private UpdateService $updates;

    public function __construct()
    {
        Auth::requireLogin();
        $this->settings = new SettingRepository();
        $this->portals = new PortalRepository();
        $this->importer = new ImportService();
        $this->updates = new UpdateService();
    }

    public function index(): void
    {
        echo View::render('admin/index', [
            'formSettings' => $this->settings->all(),
            'portals' => $this->portals->all(),
        ]);
    }

    public function updateSettings(): void
    {
        $siteName = trim((string) ($_POST['site_name'] ?? ''));
        $contactEmail = trim((string) ($_POST['contact_email'] ?? ''));
        $defaultCurrency = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', (string) ($_POST['default_currency'] ?? '')), 0, 3));
        $supportPhone = trim((string) ($_POST['support_phone'] ?? ''));
        $feedNote = trim((string) ($_POST['feed_note'] ?? ''));

        if ($siteName === '') {
            flash('El nombre del sitio es obligatorio.');
            redirect('/admin');
        }

        if ($contactEmail !== '' && filter_var($contactEmail, FILTER_VALIDATE_EMAIL) === false) {
            flash('El correo de contacto no tiene un formato válido.');
            redirect('/admin');
        }

        if ($defaultCurrency === '') {
            $defaultCurrency = 'EUR';
        }

        $this->settings->updateMany([
            'site_name' => $siteName,
            'contact_email' => $contactEmail,
            'default_currency' => $defaultCurrency,
            'support_phone' => $supportPhone,
            'feed_note' => $feedNote,
        ]);

        flash('Configuración general actualizada.');
        redirect('/admin');
    }

    public function updatePortals(): void
    {
        $input = $_POST['portals'] ?? [];
        $updates = [];

        foreach ($input as $id => $payload) {
            $portalId = (int) $id;
            $endpoint = trim((string) ($payload['endpoint'] ?? ''));
            $authToken = trim((string) ($payload['auth_token'] ?? ''));
            $enabled = isset($payload['enabled']) ? 1 : 0;

            $updates[] = [
                'id' => $portalId,
                'endpoint' => $endpoint !== '' ? $endpoint : null,
                'auth_token' => $authToken !== '' ? $authToken : null,
                'enabled' => $enabled,
            ];
        }

        if ($updates !== []) {
            $this->portals->updateAll($updates);
            flash('Portales actualizados correctamente.');
        } else {
            flash('No se recibieron cambios de portales.');
        }

        redirect('/admin');
    }

    public function importXml(): void
    {
        $xmlContent = '';

        if (isset($_FILES['xml_file']) && is_array($_FILES['xml_file']) && ($_FILES['xml_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $xmlContent = (string) file_get_contents($_FILES['xml_file']['tmp_name']);
        } elseif (!empty($_POST['xml_payload'])) {
            $xmlContent = trim((string) $_POST['xml_payload']);
        }

        if ($xmlContent === '') {
            flash('Debes seleccionar un archivo XML o pegar el contenido para importarlo.');
            redirect('/admin');
        }

        try {
            $count = $this->importer->importFromString($xmlContent);
            flash("Se importaron {$count} propiedades desde el XML proporcionado.");
        } catch (\Throwable $e) {
            flash('Error al importar XML: ' . $e->getMessage());
        }

        redirect('/admin');
    }

    public function uploadUpdate(): void
    {
        if (!class_exists('ZipArchive')) {
            flash('El servidor no tiene habilitada la extensión zip.');
            redirect('/admin');
        }

        if (!isset($_FILES['update_package']) || !is_array($_FILES['update_package'])) {
            flash('Debes seleccionar un archivo ZIP válido.');
            redirect('/admin');
        }

        $file = $_FILES['update_package'];
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            flash('No se pudo subir el archivo ZIP (código de error: ' . $error . ').');
            redirect('/admin');
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_file($tmpPath)) {
            flash('No se encontró el archivo temporal de la actualización.');
            redirect('/admin');
        }

        $storageUpdates = BASE_PATH . '/storage/updates';
        if (!is_dir($storageUpdates) && !@mkdir($storageUpdates, 0775, true) && !is_dir($storageUpdates)) {
            flash('No se pudo preparar la carpeta de actualizaciones.');
            redirect('/admin');
        }

        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', (string) ($file['name'] ?? 'update.zip'));
        $target = $storageUpdates . '/' . date('Ymd_His') . '_' . $filename;

        if (!@move_uploaded_file($tmpPath, $target)) {
            if (!@rename($tmpPath, $target)) {
                flash('No se pudo almacenar el paquete de actualización.');
                redirect('/admin');
            }
        }

        try {
            $applied = $this->updates->apply($target, BASE_PATH);
            flash("Actualización aplicada correctamente ({$applied} archivos actualizados).");
        } catch (\Throwable $e) {
            flash('No se pudo aplicar la actualización: ' . $e->getMessage());
        }

        redirect('/admin');
    }
}
