<?php
$title = 'Acceso al panel';
ob_start();
?>
<section class="auth-card">
    <header class="auth-card__header">
        <h1>Panel inmobiliario</h1>
        <p>Introduce tus credenciales para administrar el inventario y los portales.</p>
    </header>
    <form method="post" action="/login" class="auth-form">
        <?= csrf_field(); ?>
        <label>Usuario
            <input type="text" name="username" required autofocus autocomplete="username" placeholder="tu.usuario">
        </label>
        <label>Contraseña
            <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
        </label>
        <button type="submit" class="button button--primary button--full">Entrar</button>
    </form>
    <footer class="auth-card__footer">
        <small>¿Problemas para acceder? Contacta con el administrador del sistema.</small>
    </footer>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
