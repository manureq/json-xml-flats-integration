<?php
$title = 'Propiedades';
ob_start();
?>
<section class="panel">
    <header class="panel-header">
        <h2>Listado de propiedades</h2>
        <a class="button primary" href="/properties/create">Nueva propiedad</a>
    </header>
    <table>
        <thead>
        <tr>
            <th>Título</th>
            <th>Tipo</th>
            <th>Ciudad</th>
            <th>Precio</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if ($properties === []): ?>
            <tr>
                <td colspan="5" class="muted">No hay propiedades registradas todavía.</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($properties as $property): ?>
            <tr>
                <td><?= htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($property['type'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($property['city'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <?php if ($property['rent_price'] !== null): ?>
                        <?= number_format((float) $property['rent_price'], 2); ?> <?= htmlspecialchars($property['currency'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    <?php else: ?>
                        N/D
                    <?php endif; ?>
                </td>
                <td class="table-actions">
                    <a class="link" href="/properties?id=<?= (int) $property['id']; ?>">Ver</a>
                    <a class="link" href="/properties/edit?id=<?= (int) $property['id']; ?>">Editar</a>
                    <form method="post" action="/properties/delete" onsubmit="return confirm('¿Eliminar propiedad?');">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= (int) $property['id']; ?>">
                        <button type="submit" class="link danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
