<?php

declare(strict_types=1);

namespace App\Application\Update\Handlers;

use App\Application\Update\Commands\ApplyUpdateCommand;
use App\Application\Update\DTOs\UpdateResult;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ApplyUpdateHandler
{
    public function __construct(
        private readonly Kernel $artisan,
    ) {}

    /**
     * Handle the apply update command.
     */
    public function handle(ApplyUpdateCommand $command): UpdateResult
    {
        $logs = [];
        $currentVersion = config('version.current', '1.0.0');

        try {
            $logs[] = "Iniciando actualización a v{$command->targetVersion}...";

            // Verify update file exists
            if (!file_exists($command->updateFilePath)) {
                return UpdateResult::failure(
                    'El archivo de actualización no existe.',
                    ['Archivo no encontrado: ' . $command->updateFilePath],
                    $logs,
                );
            }

            // Enable maintenance mode
            $logs[] = "Activando modo mantenimiento...";
            $this->runArtisanCommand('down', ['--secret' => 'update-in-progress']);
            $logs[] = "Modo mantenimiento activado.";

            // Extract update
            $logs[] = "Extrayendo archivos...";
            $extractResult = $this->extractUpdate($command->updateFilePath);

            if (!$extractResult['success']) {
                $this->runArtisanCommand('up');
                return UpdateResult::failure(
                    'Error al extraer la actualización.',
                    $extractResult['errors'],
                    array_merge($logs, $extractResult['logs']),
                );
            }

            $logs = array_merge($logs, $extractResult['logs']);

            // Update version in config file
            $logs[] = "Actualizando versión en configuración...";
            $this->updateVersionConfig($command->targetVersion);
            $logs[] = "Versión actualizada a {$command->targetVersion}.";

            // Run migrations
            $logs[] = "Ejecutando migraciones...";
            $migrateResult = $this->runArtisanCommand('migrate', ['--force' => true]);
            $logs[] = "Migraciones completadas.";
            if ($migrateResult) {
                $logs[] = $migrateResult;
            }

            // Clear all caches
            $logs[] = "Limpiando cachés...";
            $this->runArtisanCommand('config:clear');
            $this->runArtisanCommand('route:clear');
            $this->runArtisanCommand('view:clear');
            $this->runArtisanCommand('cache:clear');
            $logs[] = "Cachés limpiadas.";

            // Optimize for production
            $logs[] = "Optimizando aplicación...";
            $this->runArtisanCommand('config:cache');
            $this->runArtisanCommand('route:cache');
            $logs[] = "Aplicación optimizada.";

            // Disable maintenance mode
            $logs[] = "Desactivando modo mantenimiento...";
            $this->runArtisanCommand('up');
            $logs[] = "Modo mantenimiento desactivado.";

            // Cleanup update file
            if (file_exists($command->updateFilePath)) {
                unlink($command->updateFilePath);
                $logs[] = "Archivo de actualización eliminado.";
            }

            $logs[] = "¡Actualización completada exitosamente!";

            Log::info('Update applied successfully', [
                'from' => $currentVersion,
                'to' => $command->targetVersion,
            ]);

            return UpdateResult::success(
                "Actualización a v{$command->targetVersion} completada.",
                fromVersion: $currentVersion,
                toVersion: $command->targetVersion,
                logs: $logs,
                backupPath: $command->backupPath,
            );

        } catch (\Exception $e) {
            Log::error('Error applying update: ' . $e->getMessage());

            // Try to restore from backup if available
            if ($command->backupPath && is_dir($command->backupPath)) {
                $logs[] = "Error detectado. Intentando restaurar desde backup...";
                $restoreResult = $this->restoreFromBackup($command->backupPath);
                $logs = array_merge($logs, $restoreResult['logs']);
            }

            // Ensure maintenance mode is off
            try {
                $this->runArtisanCommand('up');
            } catch (\Exception $upError) {
                // Ignore
            }

            return UpdateResult::failure(
                'Error al aplicar la actualización.',
                [$e->getMessage()],
                $logs,
            );
        }
    }

    /**
     * Extract the update ZIP file.
     */
    private function extractUpdate(string $zipPath): array
    {
        $logs = [];
        $errors = [];

        $zip = new \ZipArchive();
        $result = $zip->open($zipPath);

        if ($result !== true) {
            return [
                'success' => false,
                'logs' => $logs,
                'errors' => ['No se pudo abrir el archivo ZIP: código ' . $result],
            ];
        }

        $logs[] = "ZIP abierto: {$zip->numFiles} archivos.";

        // Get preserved paths from config
        $preservedPaths = config('version.preserved_paths', [
            '.env',
            'storage/app/public',
            'storage/app/backups',
            'storage/app/updates',
            'storage/logs',
        ]);

        $basePath = base_path();
        $tempDir = storage_path('app/updates/temp_extract_' . time());

        // Extract to temp directory first
        if (!mkdir($tempDir, 0755, true)) {
            $zip->close();
            return [
                'success' => false,
                'logs' => $logs,
                'errors' => ['No se pudo crear directorio temporal.'],
            ];
        }

        $zip->extractTo($tempDir);
        $zip->close();

        $logs[] = "Archivos extraídos a directorio temporal.";

        // Find the root directory in the extracted files (GitHub adds a folder)
        $extractedDirs = glob($tempDir . '/*', GLOB_ONLYDIR);
        $sourceDir = count($extractedDirs) === 1 ? $extractedDirs[0] : $tempDir;

        // Copy files, respecting preserved paths
        $this->copyUpdateFiles($sourceDir, $basePath, $preservedPaths, $logs);

        // Cleanup temp directory
        $this->deleteDirectory($tempDir);
        $logs[] = "Directorio temporal eliminado.";

        return [
            'success' => true,
            'logs' => $logs,
            'errors' => [],
        ];
    }

    /**
     * Copy update files to destination, preserving certain paths.
     */
    private function copyUpdateFiles(string $source, string $dest, array $preservedPaths, array &$logs): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $copiedCount = 0;
        $skippedCount = 0;

        foreach ($iterator as $item) {
            $relativePath = str_replace($source . '/', '', $item->getPathname());

            // Check if path should be preserved
            $shouldPreserve = false;
            foreach ($preservedPaths as $preserved) {
                if (str_starts_with($relativePath, $preserved)) {
                    $shouldPreserve = true;
                    break;
                }
            }

            if ($shouldPreserve) {
                $skippedCount++;
                continue;
            }

            $destPath = $dest . '/' . $relativePath;

            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                $destDir = dirname($destPath);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                copy($item->getPathname(), $destPath);
                $copiedCount++;
            }
        }

        $logs[] = "Archivos copiados: {$copiedCount}, omitidos: {$skippedCount}.";
    }

    /**
     * Update the version in config/version.php.
     */
    private function updateVersionConfig(string $newVersion): void
    {
        $configPath = config_path('version.php');
        $content = file_get_contents($configPath);

        // Replace the version string
        $content = preg_replace(
            "/'current'\s*=>\s*'[^']+'/",
            "'current' => '{$newVersion}'",
            $content
        );

        file_put_contents($configPath, $content);
    }

    /**
     * Run an Artisan command.
     */
    private function runArtisanCommand(string $command, array $parameters = []): ?string
    {
        try {
            Artisan::call($command, $parameters);
            return Artisan::output();
        } catch (\Exception $e) {
            Log::warning("Artisan command failed: {$command}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Restore from backup.
     */
    private function restoreFromBackup(string $backupPath): array
    {
        $logs = [];

        try {
            // Restore .env
            $envBackup = $backupPath . '/.env';
            if (file_exists($envBackup)) {
                copy($envBackup, base_path('.env'));
                $logs[] = "Archivo .env restaurado.";
            }

            // Restore version.php
            $versionBackup = $backupPath . '/config/version.php';
            if (file_exists($versionBackup)) {
                copy($versionBackup, config_path('version.php'));
                $logs[] = "Archivo version.php restaurado.";
            }

            $logs[] = "Restauración parcial completada.";
            $logs[] = "NOTA: Puede ser necesario restaurar la BD manualmente.";

        } catch (\Exception $e) {
            $logs[] = "Error durante restauración: " . $e->getMessage();
        }

        return ['logs' => $logs];
    }

    /**
     * Delete a directory recursively.
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
