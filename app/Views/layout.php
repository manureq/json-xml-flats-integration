<?php
$siteName = $settings['site_name'] ?? 'Inmobiliaria Segura';
$contactEmail = $settings['contact_email'] ?? '';
$supportPhone = $settings['support_phone'] ?? '';
$isAuthenticated = (bool) ($auth['check'] ?? false);
$username = $auth['user']['username'] ?? '';
$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? $siteName, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body class="<?= $isAuthenticated ? 'with-sidebar' : 'public-screen'; ?>">
<?php if ($isAuthenticated): ?>
    <aside class="sidebar">
        <div class="sidebar__brand">
            <span class="brand-icon" aria-hidden="true">🏢</span>
            <div>
                <strong><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></strong>
                <small>Administración</small>
            </div>
        </div>
        <div class="sidebar__user">
            <span class="avatar" aria-hidden="true"><?= strtoupper(substr($username, 0, 1)); ?></span>
            <div>
                <span class="name"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
                <small>Sesión activa</small>
            </div>
        </div>
        <nav class="sidebar__nav">
            <a href="/" <?= $requestUri === '/' ? 'class="active"' : ''; ?>>Resumen</a>
            <a href="/properties" <?= str_starts_with($requestUri, '/properties') ? 'class="active"' : ''; ?>>Propiedades</a>
            <a href="/properties/create">Nueva propiedad</a>
            <a href="/admin" <?= str_starts_with($requestUri, '/admin') ? 'class="active"' : ''; ?>>Administración</a>
            <a href="/feeds/json" target="_blank" rel="noopener">Feed JSON</a>
            <a href="/feeds/xml" target="_blank" rel="noopener">Feed XML</a>
        </nav>
        <form method="post" action="/logout" class="sidebar__logout">
            <?= csrf_field(); ?>
            <button type="submit">Cerrar sesión</button>
        </form>
    </aside>
<?php endif; ?>

<div class="app-surface">
    <header class="topbar">
        <div>
            <h1><?= htmlspecialchars($title ?? $siteName, ENT_QUOTES, 'UTF-8'); ?></h1>
            <?php if ($isAuthenticated): ?>
                <p>Gestiona propiedades, portales y feeds desde un panel seguro.</p>
            <?php else: ?>
                <p>Autentícate para acceder al panel inmobiliario.</p>
            <?php endif; ?>
        </div>
        <?php if ($isAuthenticated && $contactEmail !== ''): ?>
            <div class="topbar__meta">
                <span>Soporte</span>
                <a href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?></a>
                <?php if ($supportPhone !== ''): ?>
                    <span><?= htmlspecialchars($supportPhone, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </header>

    <main class="content">
        <?php if (!empty($flash)): ?>
            <div class="alert"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?= $content ?? ''; ?>
    </main>

    <footer class="footer">
        <small>
            &copy; <?= date('Y'); ?> <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>.
            <?php if ($contactEmail !== ''): ?>
                Contacto: <a href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?></a>
            <?php endif; ?>
            <?php if ($supportPhone !== ''): ?>
                · Tel: <?= htmlspecialchars($supportPhone, ENT_QUOTES, 'UTF-8'); ?>
            <?php endif; ?>
        </small>
    </footer>
</div>
</body>
</html>
