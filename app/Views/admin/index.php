<?php
$title = 'Administración';
$values = $formSettings ?? [];
ob_start();
?>
<section class="card">
    <h1>Configuración del sitio</h1>
    <form method="post" action="/admin/settings">
        <?= csrf_field(); ?>
        <div class="grid">
            <label>Nombre del sitio
                <input type="text" name="site_name" required value="<?= htmlspecialchars($values['site_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Correo de contacto
                <input type="email" name="contact_email" value="<?= htmlspecialchars($values['contact_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Moneda por defecto
                <input type="text" name="default_currency" maxlength="3" value="<?= htmlspecialchars($values['default_currency'] ?? 'EUR', ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Teléfono de soporte
                <input type="text" name="support_phone" value="<?= htmlspecialchars($values['support_phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </label>
        </div>
        <label>Nota para feeds (se añade al JSON/XML)
            <textarea name="feed_note" rows="3" placeholder="Información adicional para portales."><?= htmlspecialchars($values['feed_note'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </label>
        <button class="button" type="submit">Guardar configuración</button>
    </form>
</section>
<section class="card">
    <h2>Portales conectados</h2>
    <form method="post" action="/admin/portals">
        <?= csrf_field(); ?>
        <table class="table">
            <thead>
            <tr>
                <th>Portal</th>
                <th>Endpoint</th>
                <th>Token</th>
                <th>Activo</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($portals as $portal): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($portal['name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                        <small class="muted">Slug: <?= htmlspecialchars($portal['slug'], ENT_QUOTES, 'UTF-8'); ?></small>
                    </td>
                    <td>
                        <input type="url" name="portals[<?= (int) $portal['id']; ?>][endpoint]" value="<?= htmlspecialchars((string) ($portal['endpoint'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://api.portal.com/feed">
                    </td>
                    <td>
                        <input type="text" name="portals[<?= (int) $portal['id']; ?>][auth_token]" value="<?= htmlspecialchars((string) ($portal['auth_token'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Opcional">
                    </td>
                    <td class="center">
                        <label class="switch">
                            <input type="checkbox" name="portals[<?= (int) $portal['id']; ?>][enabled]" value="1" <?= ((int) $portal['enabled']) === 1 ? 'checked' : ''; ?>>
                            <span>Activo</span>
                        </label>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button class="button" type="submit">Actualizar portales</button>
    </form>
</section>
<section class="card">
    <h2>Importar propiedades desde XML</h2>
    <form method="post" action="/admin/import" enctype="multipart/form-data" class="import-form">
        <?= csrf_field(); ?>
        <div class="grid">
            <label>Archivo XML
                <input type="file" name="xml_file" accept="text/xml,application/xml">
            </label>
            <label>O pega el contenido
                <textarea name="xml_payload" rows="6" placeholder="&lt;properties&gt;...&lt;/properties&gt;"></textarea>
            </label>
        </div>
        <p class="muted">Se validará la firma de cada nodo y se actualizarán/añadirán propiedades según su identificador externo.</p>
        <button class="button" type="submit">Importar XML</button>
    </form>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
