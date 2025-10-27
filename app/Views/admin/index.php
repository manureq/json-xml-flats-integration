<?php
$title = 'Administración';
$values = $formSettings ?? [];
ob_start();
?>
<section class="panel-grid">
    <article class="panel">
        <header>
            <h2>Identidad del sitio</h2>
            <p>Estos datos aparecen en el panel y en los feeds exportados.</p>
        </header>
        <form method="post" action="/admin/settings" class="form-grid">
            <?= csrf_field(); ?>
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
            <label class="full">Nota para feeds
                <textarea name="feed_note" rows="3" placeholder="Información adicional para portales."><?= htmlspecialchars($values['feed_note'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </label>
            <div class="actions">
                <button class="button primary" type="submit">Guardar cambios</button>
            </div>
        </form>
    </article>

    <article class="panel">
        <header>
            <h2>Sincronización con portales</h2>
            <p>Configura endpoints, tokens y habilita solo los portales que utilices.</p>
        </header>
        <form method="post" action="/admin/portals" class="table-form">
            <?= csrf_field(); ?>
            <table>
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
                            <strong><?= htmlspecialchars($portal['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
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
            <div class="actions end">
                <button class="button" type="submit">Actualizar portales</button>
            </div>
        </form>
    </article>
</section>

<section class="panel-grid">
    <article class="panel">
        <header>
            <h2>Importar propiedades</h2>
            <p>Sube un XML o pega el contenido para actualizar el inventario.</p>
        </header>
        <form method="post" action="/admin/import" enctype="multipart/form-data" class="import-form">
            <?= csrf_field(); ?>
            <div class="form-grid">
                <label>Archivo XML
                    <input type="file" name="xml_file" accept="text/xml,application/xml">
                </label>
                <label>Contenido XML
                    <textarea name="xml_payload" rows="6" placeholder="&lt;properties&gt;...&lt;/properties&gt;"></textarea>
                </label>
            </div>
            <p class="muted">Se validará la firma de cada nodo y se sincronizarán las propiedades según su identificador externo.</p>
            <div class="actions">
                <button class="button" type="submit">Importar XML</button>
            </div>
        </form>
    </article>

    <article class="panel highlight">
        <header>
            <h2>Actualizar plataforma</h2>
            <p>Sube un paquete ZIP firmado con la última versión del panel para desplegarlo desde el navegador.</p>
        </header>
        <form method="post" action="/admin/update" enctype="multipart/form-data" class="update-form">
            <?= csrf_field(); ?>
            <label>Paquete ZIP
                <input type="file" name="update_package" accept="application/zip" required>
            </label>
            <p class="muted">El sistema verificará rutas seguras antes de aplicar la actualización sobre la instalación actual.</p>
            <div class="actions">
                <button class="button primary" type="submit">Aplicar actualización</button>
            </div>
        </form>
    </article>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
