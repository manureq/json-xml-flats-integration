<?php
$title = 'Panel de control';
ob_start();
?>
<section class="page-header">
    <div>
        <h2>Resumen general</h2>
        <p>Consulta el pulso de tu inventario y actúa con accesos rápidos.</p>
    </div>
    <div class="page-header__actions">
        <a class="button button--primary" href="/properties/create">Añadir propiedad</a>
        <a class="button" href="/admin">Configurar portales</a>
    </div>
</section>

<section class="panel">
    <div class="panel__header">
        <div>
            <h3>Indicadores clave</h3>
            <p>Datos al día para decidir dónde publicar y qué actualizar.</p>
        </div>
    </div>
    <div class="panel__body">
        <div class="metric-grid">
            <article class="metric">
                <span class="metric__value"><?= (int) ($stats['total'] ?? 0); ?></span>
                <span class="metric__label">Propiedades registradas</span>
            </article>
            <article class="metric">
                <span class="metric__value"><?= (int) ($stats['available'] ?? 0); ?></span>
                <span class="metric__label">Disponibles hoy</span>
            </article>
            <article class="metric">
                <span class="metric__value">
                    <?= $stats['avg_price'] !== null ? number_format((float) $stats['avg_price'], 2) : 'N/D'; ?>
                </span>
                <span class="metric__label">Precio promedio mensual</span>
            </article>
        </div>
        <p class="panel__hint">Administra tus propiedades, controla la difusión por portales y genera feeds fiables en formatos JSON y XML.</p>
    </div>
</section>

<section class="panel">
    <div class="panel__header">
        <div>
            <h3>Datos del sitio</h3>
            <p>Lo que verán los portales y tus clientes cuando consumen los feeds.</p>
        </div>
    </div>
    <div class="panel__body">
        <dl class="details-grid">
            <div>
                <dt>Nombre comercial</dt>
                <dd><?= htmlspecialchars($settings['site_name'] ?? 'Inmobiliaria Segura', ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div>
                <dt>Correo de contacto</dt>
                <dd><?= htmlspecialchars($settings['contact_email'] ?? 'No definido', ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div>
                <dt>Moneda por defecto</dt>
                <dd><?= htmlspecialchars($settings['default_currency'] ?? 'EUR', ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div>
                <dt>Teléfono de soporte</dt>
                <dd><?= htmlspecialchars($settings['support_phone'] ?? 'No definido', ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
        </dl>
        <p class="panel__hint">Puedes modificar estos datos desde la sección de <a href="/admin">administración</a>.</p>
    </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
