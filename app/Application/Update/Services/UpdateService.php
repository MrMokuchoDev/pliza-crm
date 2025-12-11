<?php

declare(strict_types=1);

namespace App\Application\Update\Services;

use App\Application\Update\Commands\ApplyUpdateCommand;
use App\Application\Update\Commands\CheckForUpdatesCommand;
use App\Application\Update\Commands\CreateBackupCommand;
use App\Application\Update\Commands\DownloadUpdateCommand;
use App\Application\Update\DTOs\UpdateCheckResult;
use App\Application\Update\DTOs\UpdateResult;
use App\Application\Update\Handlers\ApplyUpdateHandler;
use App\Application\Update\Handlers\CheckForUpdatesHandler;
use App\Application\Update\Handlers\CreateBackupHandler;
use App\Application\Update\Handlers\DownloadUpdateHandler;

class UpdateService
{
    public function __construct(
        private readonly CheckForUpdatesHandler $checkHandler,
        private readonly DownloadUpdateHandler $downloadHandler,
        private readonly CreateBackupHandler $backupHandler,
        private readonly ApplyUpdateHandler $applyHandler,
    ) {}

    /**
     * Check for available updates.
     */
    public function checkForUpdates(bool $forceRefresh = false): UpdateCheckResult
    {
        $command = new CheckForUpdatesCommand($forceRefresh);
        return $this->checkHandler->handle($command);
    }

    /**
     * Download an update.
     */
    public function downloadUpdate(string $version, string $downloadUrl): UpdateResult
    {
        $command = new DownloadUpdateCommand($version, $downloadUrl);
        return $this->downloadHandler->handle($command);
    }

    /**
     * Create a backup before updating.
     */
    public function createBackup(bool $includeDatabase = true, bool $includeUploads = true): UpdateResult
    {
        $command = new CreateBackupCommand($includeDatabase, $includeUploads);
        return $this->backupHandler->handle($command);
    }

    /**
     * Apply an update.
     */
    public function applyUpdate(string $updateFilePath, string $targetVersion, ?string $backupPath = null): UpdateResult
    {
        $command = new ApplyUpdateCommand($updateFilePath, $targetVersion, $backupPath);
        return $this->applyHandler->handle($command);
    }

    /**
     * Get path to downloaded update file.
     */
    public function getUpdateFilePath(string $version): ?string
    {
        return $this->downloadHandler->getUpdateFilePath($version);
    }

    /**
     * Get list of available backups.
     */
    public function getBackups(): array
    {
        return $this->backupHandler->getBackups();
    }

    /**
     * Get the current version.
     */
    public function getCurrentVersion(): string
    {
        return config('version.current', '1.0.0');
    }

    /**
     * Get the last time updates were checked.
     */
    public function getLastCheckTimestamp(): ?\DateTimeInterface
    {
        return $this->checkHandler->getLastCheckTimestamp();
    }

    /**
     * Perform a full update process.
     */
    public function performFullUpdate(string $version, string $downloadUrl): UpdateResult
    {
        $logs = [];
        $backupPath = null;

        // Step 1: Create backup
        $backupResult = $this->createBackup();
        $logs = array_merge($logs, $backupResult->logs);

        if (!$backupResult->success) {
            return UpdateResult::failure(
                'Error al crear backup antes de actualizar.',
                $backupResult->errors,
                $logs,
            );
        }

        $backupPath = $backupResult->backupPath;

        // Step 2: Download update
        $downloadResult = $this->downloadUpdate($version, $downloadUrl);
        $logs = array_merge($logs, $downloadResult->logs);

        if (!$downloadResult->success) {
            return UpdateResult::failure(
                'Error al descargar la actualización.',
                $downloadResult->errors,
                $logs,
            );
        }

        // Step 3: Apply update
        $updateFilePath = $this->getUpdateFilePath($version);

        if (!$updateFilePath) {
            return UpdateResult::failure(
                'No se encontró el archivo de actualización descargado.',
                ['Archivo no encontrado después de la descarga.'],
                $logs,
            );
        }

        $applyResult = $this->applyUpdate($updateFilePath, $version, $backupPath);
        $logs = array_merge($logs, $applyResult->logs);

        if (!$applyResult->success) {
            return UpdateResult::failure(
                'Error al aplicar la actualización.',
                $applyResult->errors,
                $logs,
            );
        }

        return UpdateResult::success(
            "Actualización a v{$version} completada exitosamente.",
            fromVersion: $applyResult->fromVersion,
            toVersion: $version,
            logs: $logs,
            backupPath: $backupPath,
        );
    }

    /**
     * Cleanup old updates and backups.
     */
    public function cleanup(): void
    {
        $this->downloadHandler->cleanupOldUpdates();
    }
}
