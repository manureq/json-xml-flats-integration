<?php
$title = 'Panel de control';
ob_start();
?>
<section class="card">
    <h1>Resumen general</h1>
    <div class="stats">
        <div>
            <span class="value"><?= (int) ($stats['total'] ?? 0); ?></span>
            <span class="label">Propiedades registradas</span>
        </div>
        <div>
            <span class="value"><?= (int) ($stats['available'] ?? 0); ?></span>
            <span class="label">Disponibles hoy</span>
        </div>
        <div>
            <span class="value">
                <?= $stats['avg_price'] !== null ? number_format((float) $stats['avg_price'], 2) : 'N/D'; ?>
            </span>
            <span class="label">Precio promedio</span>
        </div>
    </div>
    <p>Administra tus propiedades, controla la difusión por portales y genera feeds fiables en formatos JSON y XML con protección
CSRF y sanitización en cada formulario.</p>
</section>
<section class="card">
    <h2>Datos del sitio</h2>
    <dl class="details">
        <div><dt>Nombre comercial</dt><dd><?= htmlspecialchars($settings['site_name'] ?? 'Inmobiliaria Segura', ENT_QUOTES, 'UTF-8'); ?></dd></div>
        <div><dt>Correo de contacto</dt><dd><?= htmlspecialchars($settings['contact_email'] ?? 'No definido', ENT_QUOTES, 'UTF-8'); ?></dd></div>
        <div><dt>Moneda por defecto</dt><dd><?= htmlspecialchars($settings['default_currency'] ?? 'EUR', ENT_QUOTES, 'UTF-8'); ?></dd></div>
        <div><dt>Teléfono de soporte</dt><dd><?= htmlspecialchars($settings['support_phone'] ?? 'No definido', ENT_QUOTES, 'UTF-8'); ?></dd></div>
    </dl>
    <p>Actualiza estos datos en el panel de <a href="/admin">administración</a> para que se reflejen automáticamente en los feeds y la interfaz.</p>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
