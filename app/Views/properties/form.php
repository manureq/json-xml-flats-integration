<?php
$title = $property ? 'Editar propiedad' : 'Crear propiedad';
$defaultCurrency = $settings['default_currency'] ?? 'EUR';
$currencyValue = $property['currency'] ?? $defaultCurrency;
ob_start();
?>
<section class="card">
    <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
    <form method="post" action="<?= $property ? '/properties/update' : '/properties'; ?>">
        <?= csrf_field(); ?>
        <?php if ($property): ?>
            <input type="hidden" name="id" value="<?= (int) $property['id']; ?>">
        <?php endif; ?>
        <div class="grid">
            <label>Título
                <input type="text" name="title" required value="<?= htmlspecialchars($property['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Identificador externo
                <input type="text" name="external_id" value="<?= htmlspecialchars($property['external_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Tipo
                <input type="text" name="type" value="<?= htmlspecialchars($property['type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Dirección
                <input type="text" name="address" value="<?= htmlspecialchars($property['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Ciudad
                <input type="text" name="city" value="<?= htmlspecialchars($property['city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>País
                <input type="text" name="country" value="<?= htmlspecialchars($property['country'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Latitud
                <input type="number" step="any" name="latitude" value="<?= htmlspecialchars((string) ($property['latitude'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Longitud
                <input type="number" step="any" name="longitude" value="<?= htmlspecialchars((string) ($property['longitude'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Precio mensual
                <input type="number" step="0.01" name="rent_price" value="<?= htmlspecialchars((string) ($property['rent_price'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Moneda
                <input type="text" name="currency" value="<?= htmlspecialchars($currencyValue, ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Dormitorios
                <input type="number" name="bedrooms" value="<?= htmlspecialchars((string) ($property['bedrooms'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Baños
                <input type="number" name="bathrooms" value="<?= htmlspecialchars((string) ($property['bathrooms'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Área (m²)
                <input type="number" step="0.1" name="area" value="<?= htmlspecialchars((string) ($property['area'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>Disponible desde
                <input type="date" name="available_from" value="<?= htmlspecialchars($property['available_from'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </label>
        </div>
        <label>Descripción
            <textarea name="description" rows="6"><?= htmlspecialchars($property['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </label>

        <fieldset>
            <legend>Amenities</legend>
            <div id="amenities">
                <?php $existingAmenities = $amenities ?? ($property['amenities'] ?? []); ?>
                <?php if ($existingAmenities === []): ?>
                    <div class="group">
                        <input type="text" name="amenities[]" placeholder="Wifi, terraza, ascensor…">
                    </div>
                <?php else: ?>
                    <?php foreach ($existingAmenities as $amenity): ?>
                        <?php $value = is_array($amenity) ? ($amenity['name'] ?? '') : $amenity; ?>
                        <div class="group">
                            <input type="text" name="amenities[]" value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    <?php endforeach; ?>
                    <div class="group">
                        <input type="text" name="amenities[]" placeholder="Agregar amenity">
                    </div>
                <?php endif; ?>
            </div>
        </fieldset>

        <fieldset>
            <legend>Multimedia</legend>
            <div id="media-items">
                <?php $existingMedia = $media ?? ($property['media'] ?? []); ?>
                <?php if ($existingMedia === []): ?>
                    <div class="group">
                        <input type="url" name="media_url[]" placeholder="https://...">
                        <select name="media_type[]">
                            <option value="image">Imagen</option>
                            <option value="video">Video</option>
                        </select>
                        <input type="text" name="media_caption[]" placeholder="Descripción">
                    </div>
                <?php else: ?>
                    <?php foreach ($existingMedia as $item): ?>
                        <div class="group">
                            <input type="url" name="media_url[]" value="<?= htmlspecialchars($item['url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <select name="media_type[]">
                                <?php $type = $item['type'] ?? 'image'; ?>
                                <option value="image" <?= $type === 'image' ? 'selected' : ''; ?>>Imagen</option>
                                <option value="video" <?= $type === 'video' ? 'selected' : ''; ?>>Video</option>
                            </select>
                            <input type="text" name="media_caption[]" value="<?= htmlspecialchars($item['caption'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    <?php endforeach; ?>
                    <div class="group">
                        <input type="url" name="media_url[]" placeholder="https://...">
                        <select name="media_type[]">
                            <option value="image">Imagen</option>
                            <option value="video">Video</option>
                        </select>
                        <input type="text" name="media_caption[]" placeholder="Descripción">
                    </div>
                <?php endif; ?>
            </div>
        </fieldset>

        <fieldset>
            <legend>Portales</legend>
            <div class="grid portals">
                <?php foreach ($portals as $portal): ?>
                    <?php $checked = !empty($portal['selected']); ?>
                    <label>
                        <input type="checkbox" name="portals[]" value="<?= htmlspecialchars($portal['slug'], ENT_QUOTES, 'UTF-8'); ?>" <?= $checked ? 'checked' : ''; ?>>
                        <?= htmlspecialchars($portal['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <button class="button" type="submit">Guardar</button>
    </form>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
