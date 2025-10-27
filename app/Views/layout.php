<?php
$siteName = $settings['site_name'] ?? 'Inmobiliaria Segura';
$contactEmail = $settings['contact_email'] ?? '';
$supportPhone = $settings['support_phone'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? $siteName, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <a class="brand" href="/"><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></a>
        <div class="links">
            <a href="/">Panel</a>
            <a href="/properties">Propiedades</a>
            <a href="/properties/create">Nueva propiedad</a>
            <a href="/admin">Administración</a>
            <a href="/feeds/json" target="_blank" rel="noopener">Feed JSON</a>
            <a href="/feeds/xml" target="_blank" rel="noopener">Feed XML</a>
        </div>
    </div>
</nav>
<main class="container">
    <?php if (!empty($flash)): ?>
        <div class="alert"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?= $content ?? ''; ?>
</main>
<footer class="footer">
    <div class="container">
        <small>&copy; <?= date('Y'); ?> <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>.
            <?php if ($contactEmail !== ''): ?>
                Contacto: <a href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?></a>
            <?php endif; ?>
            <?php if ($supportPhone !== ''): ?>
                · Tel: <?= htmlspecialchars($supportPhone, ENT_QUOTES, 'UTF-8'); ?>
            <?php endif; ?>
        </small>
    </div>
</footer>
</body>
</html>
