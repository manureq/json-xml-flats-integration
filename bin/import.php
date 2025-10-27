#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Services\ImportService;

if ($argc < 2) {
    fwrite(STDERR, "Uso: php bin/import.php <ruta-xml>\n");
    exit(1);
}

$path = $argv[1];
$importer = new ImportService();

try {
    $count = $importer->import($path);
    echo "Importadas {$count} propiedades." . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
