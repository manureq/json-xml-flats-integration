<?php
$title = 'Propiedades';
ob_start();
?>
<section class="page-header">
    <div>
        <h2>Propiedades</h2>
        <p>Controla tu catálogo y accede rápido a cada ficha.</p>
    </div>
    <div class="page-header__actions">
        <a class="button button--primary" href="/properties/create">Nueva propiedad</a>
        <a class="button" href="/feeds/json" target="_blank" rel="noopener">Ver feed</a>
    </div>
</section>

<section class="panel">
    <div class="panel__header">
        <div>
            <h3>Inventario activo</h3>
            <p class="panel__hint">Ordena y revisa los principales datos antes de actualizar portales.</p>
        </div>
    </div>
    <div class="panel__body">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                <tr>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Ciudad</th>
                    <th>Precio</th>
                    <th class="center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($properties === []): ?>
                    <tr>
                        <td colspan="5" class="empty">No hay propiedades registradas todavía.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($properties as $property): ?>
                    <tr>
                        <td data-label="Título">
                            <strong><?= htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php if (!empty($property['external_id'])): ?>
                                <small class="muted">ID: <?= htmlspecialchars($property['external_id'], ENT_QUOTES, 'UTF-8'); ?></small>
                            <?php endif; ?>
                        </td>
                        <td data-label="Tipo"><?= htmlspecialchars($property['type'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td data-label="Ciudad"><?= htmlspecialchars($property['city'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td data-label="Precio">
                            <?php if ($property['rent_price'] !== null): ?>
                                <?= number_format((float) $property['rent_price'], 2); ?> <?= htmlspecialchars($property['currency'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            <?php else: ?>
                                <span class="muted">N/D</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-actions" data-label="Acciones">
                            <a class="chip" href="/properties?id=<?= (int) $property['id']; ?>">Ver</a>
                            <a class="chip" href="/properties/edit?id=<?= (int) $property['id']; ?>">Editar</a>
                            <form method="post" action="/properties/delete" onsubmit="return confirm('¿Eliminar propiedad?');">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="id" value="<?= (int) $property['id']; ?>">
                                <button type="submit" class="chip chip--danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
