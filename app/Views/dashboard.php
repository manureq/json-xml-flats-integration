<?php
$title = 'Panel de control';
ob_start();
?>
<section class="panel">
    <header>
        <h2>Resumen general</h2>
        <p>Indicadores clave del inventario inmobiliario actualizado.</p>
    </header>
    <div class="stats-grid">
        <article>
            <span class="value"><?= (int) ($stats['total'] ?? 0); ?></span>
            <span class="label">Propiedades registradas</span>
        </article>
        <article>
            <span class="value"><?= (int) ($stats['available'] ?? 0); ?></span>
            <span class="label">Disponibles hoy</span>
        </article>
        <article>
            <span class="value">
                <?= $stats['avg_price'] !== null ? number_format((float) $stats['avg_price'], 2) : 'N/D'; ?>
            </span>
            <span class="label">Precio promedio</span>
        </article>
    </div>
    <p class="muted">Administra tus propiedades, controla la difusión por portales y genera feeds fiables en formatos JSON y XML.</p>
</section>

<section class="panel">
    <header>
        <h2>Datos del sitio</h2>
        <p>Resumen rápido de la información pública configurada para los feeds.</p>
    </header>
    <dl class="details-grid">
        <div><dt>Nombre comercial</dt><dd><?= htmlspecialchars($settings['site_name'] ?? 'Inmobiliaria Segura', ENT_QUOTES, 'UTF-8'); ?></dd></div>
        <div><dt>Correo de contacto</dt><dd><?= htmlspecialchars($settings['contact_email'] ?? 'No definido', ENT_QUOTES, 'UTF-8'); ?></dd></div>
        <div><dt>Moneda por defecto</dt><dd><?= htmlspecialchars($settings['default_currency'] ?? 'EUR', ENT_QUOTES, 'UTF-8'); ?></dd></div>
        <div><dt>Teléfono de soporte</dt><dd><?= htmlspecialchars($settings['support_phone'] ?? 'No definido', ENT_QUOTES, 'UTF-8'); ?></dd></div>
    </dl>
    <p class="muted">Puedes modificar estos datos desde la sección de <a href="/admin">administración</a>.</p>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
