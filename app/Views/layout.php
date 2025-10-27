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
<body class="app <?= $isAuthenticated ? 'app--secure' : 'app--public'; ?>">
<?php if ($isAuthenticated): ?>
    <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-hidden="true">
    <aside class="sidebar" aria-label="Menú principal">
        <div class="sidebar__header">
            <div class="sidebar__brand">
                <span class="brand-icon" aria-hidden="true">🏢</span>
                <div>
                    <strong><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <small>Administración</small>
                </div>
            </div>
            <label for="nav-toggle" class="sidebar__close" aria-label="Cerrar menú">&times;</label>
        </div>
        <div class="sidebar__user">
            <span class="avatar" aria-hidden="true"><?= strtoupper(substr($username, 0, 1)); ?></span>
            <div>
                <span class="name"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
                <small>Sesión activa</small>
            </div>
        </div>
        <nav class="sidebar__nav">
            <span class="sidebar__label">Panel</span>
            <a class="sidebar__link <?= $requestUri === '/' ? 'is-active' : ''; ?>" href="/">Resumen</a>
            <a class="sidebar__link <?= str_starts_with($requestUri, '/properties') ? 'is-active' : ''; ?>" href="/properties">Propiedades</a>
            <a class="sidebar__link" href="/properties/create">Nueva propiedad</a>
            <span class="sidebar__label">Operaciones</span>
            <a class="sidebar__link <?= str_starts_with($requestUri, '/admin') ? 'is-active' : ''; ?>" href="/admin">Administración</a>
            <a class="sidebar__link" href="/feeds/json" target="_blank" rel="noopener">Feed JSON</a>
            <a class="sidebar__link" href="/feeds/xml" target="_blank" rel="noopener">Feed XML</a>
        </nav>
        <form method="post" action="/logout" class="sidebar__logout">
            <?= csrf_field(); ?>
            <button type="submit" class="button button--ghost">Cerrar sesión</button>
        </form>
    </aside>
    <label for="nav-toggle" class="sidebar__overlay" aria-hidden="true"></label>
<?php endif; ?>

<div class="surface">
    <header class="topbar">
        <?php if ($isAuthenticated): ?>
            <label for="nav-toggle" class="topbar__menu" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </label>
        <?php endif; ?>
        <div class="topbar__titles">
            <p class="topbar__eyebrow"><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></p>
            <h1><?= htmlspecialchars($title ?? $siteName, ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="topbar__lead">
                <?php if ($isAuthenticated): ?>
                    Gestiona propiedades, portales y feeds con una vista clara del estado de tu inventario.
                <?php else: ?>
                    Autentícate para acceder al panel inmobiliario.
                <?php endif; ?>
            </p>
        </div>
        <?php if ($isAuthenticated && ($contactEmail !== '' || $supportPhone !== '')): ?>
            <div class="topbar__meta">
                <span class="topbar__meta-label">Soporte</span>
                <?php if ($contactEmail !== ''): ?>
                    <a class="topbar__meta-link" href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>">
                        <?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endif; ?>
                <?php if ($supportPhone !== ''): ?>
                    <span class="topbar__meta-link"><?= htmlspecialchars($supportPhone, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </header>

    <main class="content">
        <?php if (!empty($flash)): ?>
            <div class="notice notice--info"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></div>
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
