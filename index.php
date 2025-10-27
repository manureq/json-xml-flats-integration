<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

session_boot();

$router = new App\Http\Router();
$router->dispatch();
