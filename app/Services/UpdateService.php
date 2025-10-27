<?php

declare(strict_types=1);

namespace App\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use ZipArchive;

final class UpdateService
{
    public function apply(string $zipFile, string $basePath): int
    {
        if (!is_file($zipFile)) {
            throw new RuntimeException('El paquete de actualización no existe.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFile) !== true) {
            throw new RuntimeException('No se pudo abrir el archivo ZIP proporcionado.');
        }

        $tempDir = $basePath . '/storage/updates/tmp_' . bin2hex(random_bytes(8));
        if (!@mkdir($tempDir, 0775, true) && !is_dir($tempDir)) {
            $zip->close();
            throw new RuntimeException('No se pudo crear el directorio temporal para la extracción.');
        }

        if (!$zip->extractTo($tempDir)) {
            $zip->close();
            $this->deleteDirectory($tempDir);
            throw new RuntimeException('No se pudo extraer el paquete de actualización.');
        }

        $zip->close();

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $applied = 0;
        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            $relativePath = substr($item->getPathname(), strlen($tempDir) + 1);
            $relativePath = str_replace('\\', '/', $relativePath);

            if ($relativePath === '' || str_starts_with($relativePath, '__MACOSX/')) {
                continue;
            }

            if (str_contains($relativePath, '../')) {
                $this->deleteDirectory($tempDir);
                throw new RuntimeException('El paquete contiene rutas no seguras.');
            }

            $destination = rtrim($basePath, '/\\') . '/' . $relativePath;

            if ($item->isLink()) {
                $this->deleteDirectory($tempDir);
                throw new RuntimeException('El paquete contiene enlaces simbólicos no permitidos.');
            }

            if ($item->isDir()) {
                if (!is_dir($destination) && !@mkdir($destination, 0775, true) && !is_dir($destination)) {
                    $this->deleteDirectory($tempDir);
                    throw new RuntimeException('No se pudo crear el directorio de destino: ' . $destination);
                }
                continue;
            }

            $destDir = dirname($destination);
            if (!is_dir($destDir) && !@mkdir($destDir, 0775, true) && !is_dir($destDir)) {
                $this->deleteDirectory($tempDir);
                throw new RuntimeException('No se pudo preparar el directorio: ' . $destDir);
            }

            if (!@copy($item->getPathname(), $destination)) {
                $this->deleteDirectory($tempDir);
                throw new RuntimeException('Falló la copia de archivo: ' . $relativePath);
            }

            @chmod($destination, 0664);
            $applied++;
        }

        $this->deleteDirectory($tempDir);

        return $applied;
    }

    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($path);
    }
}
