<?php
$title = htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8');
ob_start();
?>
<section class="page-header">
    <div>
        <h2><?= $title; ?></h2>
        <p>Tipo: <?= htmlspecialchars($property['type'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
    <div class="page-header__actions">
        <a class="button" href="/properties">Volver al listado</a>
        <a class="button button--primary" href="/properties/edit?id=<?= (int) $property['id']; ?>">Editar ficha</a>
    </div>
</section>

<section class="panel">
    <div class="panel__body">
        <article class="summary">
            <p><?= nl2br(htmlspecialchars($property['description'] ?? 'Sin descripción registrada.', ENT_QUOTES, 'UTF-8')); ?></p>
        </article>
        <dl class="details-grid details-grid--wide">
            <div>
                <dt>Dirección</dt>
                <dd><?= htmlspecialchars($property['address'] ?? 'No especificada', ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div>
                <dt>Ciudad</dt>
                <dd><?= htmlspecialchars($property['city'] ?? 'No definida', ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div>
                <dt>País</dt>
                <dd><?= htmlspecialchars($property['country'] ?? 'No definido', ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div>
                <dt>Precio mensual</dt>
                <dd>
                    <?= $property['rent_price'] ? number_format((float) $property['rent_price'], 2) . ' ' . htmlspecialchars($property['currency'] ?? '', ENT_QUOTES, 'UTF-8') : 'N/D'; ?>
                </dd>
            </div>
            <div>
                <dt>Disponibilidad</dt>
                <dd><?= htmlspecialchars($property['available_from'] ?? 'Inmediata', ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div>
                <dt>Dormitorios</dt>
                <dd><?= htmlspecialchars((string) ($property['bedrooms'] ?? 'N/D'), ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div>
                <dt>Baños</dt>
                <dd><?= htmlspecialchars((string) ($property['bathrooms'] ?? 'N/D'), ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div>
                <dt>Área</dt>
                <dd><?= htmlspecialchars((string) ($property['area'] ?? 'N/D'), ENT_QUOTES, 'UTF-8'); ?> m²</dd>
            </div>
        </dl>

        <div class="panel__section">
            <h3>Amenities</h3>
            <ul class="chip-list">
                <?php foreach ($amenities as $amenity): ?>
                    <li><?= htmlspecialchars($amenity['name'], ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
                <?php if ($amenities === []): ?>
                    <li class="muted">Sin amenities registrados</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="panel__section">
            <h3>Multimedia</h3>
            <ul class="media-list">
                <?php foreach ($media as $item): ?>
                    <li>
                        <a href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                            <?= htmlspecialchars($item['type'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <?php if (!empty($item['caption'])): ?>
                            <small><?= htmlspecialchars($item['caption'], ENT_QUOTES, 'UTF-8'); ?></small>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                <?php if ($media === []): ?>
                    <li class="muted">Sin material multimedia</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="panel__section">
            <h3>Portales habilitados</h3>
            <ul class="chip-list">
                <?php foreach ($portals as $portal): ?>
                    <li><?= htmlspecialchars($portal['name'], ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
                <?php if ($portals === []): ?>
                    <li class="muted">No se ha asignado ningún portal</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
