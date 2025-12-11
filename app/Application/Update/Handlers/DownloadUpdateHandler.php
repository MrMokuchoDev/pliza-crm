<?php

declare(strict_types=1);

namespace App\Application\Update\Handlers;

use App\Application\Update\Commands\DownloadUpdateCommand;
use App\Application\Update\DTOs\UpdateResult;
use App\Infrastructure\External\GitHub\GitHubReleasesClient;
use Illuminate\Support\Facades\Log;

class DownloadUpdateHandler
{
    public function __construct(
        private readonly GitHubReleasesClient $githubClient,
    ) {}

    /**
     * Handle the download update command.
     */
    public function handle(DownloadUpdateCommand $command): UpdateResult
    {
        $logs = [];

        try {
            $logs[] = "Iniciando descarga de versión {$command->version}...";

            // Ensure updates directory exists
            $updatesDir = storage_path('app/updates');
            if (!is_dir($updatesDir)) {
                mkdir($updatesDir, 0755, true);
                $logs[] = "Directorio de actualizaciones creado.";
            }

            // Define destination path
            $filename = "plizacrm-v{$command->version}.zip";
            $destinationPath = $updatesDir . '/' . $filename;

            // Remove existing file if present
            if (file_exists($destinationPath)) {
                unlink($destinationPath);
                $logs[] = "Archivo existente eliminado.";
            }

            $logs[] = "Descargando desde GitHub...";

            // Download the file
            $success = $this->githubClient->downloadAsset(
                $command->downloadUrl,
                $destinationPath
            );

            if (!$success) {
                return UpdateResult::failure(
                    'Error al descargar la actualización.',
                    ['No se pudo descargar el archivo desde GitHub.'],
                    $logs,
                );
            }

            // Verify file exists and has content
            if (!file_exists($destinationPath) || filesize($destinationPath) === 0) {
                return UpdateResult::failure(
                    'El archivo descargado está vacío o corrupto.',
                    ['El archivo no se descargó correctamente.'],
                    $logs,
                );
            }

            $fileSize = $this->formatBytes(filesize($destinationPath));
            $logs[] = "Descarga completada ({$fileSize}).";
            $logs[] = "Archivo guardado en: {$destinationPath}";

            // Verify it's a valid ZIP
            $zip = new \ZipArchive();
            $zipResult = $zip->open($destinationPath);

            if ($zipResult !== true) {
                unlink($destinationPath);
                return UpdateResult::failure(
                    'El archivo descargado no es un ZIP válido.',
                    ['Error al abrir ZIP: código ' . $zipResult],
                    $logs,
                );
            }

            $numFiles = $zip->numFiles;
            $zip->close();

            $logs[] = "ZIP verificado: {$numFiles} archivos.";

            Log::info('Update downloaded successfully', [
                'version' => $command->version,
                'path' => $destinationPath,
                'size' => filesize($destinationPath),
            ]);

            return UpdateResult::success(
                "Actualización v{$command->version} descargada correctamente.",
                toVersion: $command->version,
                logs: $logs,
            );

        } catch (\Exception $e) {
            Log::error('Error downloading update: ' . $e->getMessage());

            return UpdateResult::failure(
                'Error al descargar la actualización.',
                [$e->getMessage()],
                $logs,
            );
        }
    }

    /**
     * Format bytes to human readable string.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the path of a downloaded update file.
     */
    public function getUpdateFilePath(string $version): ?string
    {
        $filename = "plizacrm-v{$version}.zip";
        $path = storage_path('app/updates/' . $filename);

        return file_exists($path) ? $path : null;
    }

    /**
     * Clean up old update files.
     */
    public function cleanupOldUpdates(int $keepLatest = 2): void
    {
        $updatesDir = storage_path('app/updates');

        if (!is_dir($updatesDir)) {
            return;
        }

        $files = glob($updatesDir . '/*.zip');

        if (count($files) <= $keepLatest) {
            return;
        }

        // Sort by modification time (newest first)
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        // Remove old files
        foreach (array_slice($files, $keepLatest) as $file) {
            unlink($file);
        }
    }
}
