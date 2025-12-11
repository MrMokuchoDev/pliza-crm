<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Updates;

use App\Application\Update\Services\UpdateService;
use Livewire\Component;

class UpdatesPanel extends Component
{
    public string $currentVersion = '';
    public ?string $latestVersion = null;
    public bool $updateAvailable = false;
    public ?array $latestRelease = null;
    public ?string $lastChecked = null;

    public bool $isChecking = false;
    public bool $isDownloading = false;
    public bool $isBackingUp = false;
    public bool $isUpdating = false;

    public array $logs = [];
    public array $backups = [];
    public ?string $error = null;

    public function mount(): void
    {
        $service = app(UpdateService::class);

        $this->currentVersion = $service->getCurrentVersion();
        $this->backups = $service->getBackups();

        $lastCheck = $service->getLastCheckTimestamp();
        $this->lastChecked = $lastCheck?->format('d/m/Y H:i');
    }

    public function checkForUpdates(): void
    {
        $this->isChecking = true;
        $this->error = null;
        $this->logs = [];

        try {
            $service = app(UpdateService::class);
            $result = $service->checkForUpdates(forceRefresh: true);

            $this->currentVersion = $result->currentVersion;
            $this->latestVersion = $result->latestVersion;
            $this->updateAvailable = $result->updateAvailable;
            $this->lastChecked = $result->lastChecked?->format('d/m/Y H:i');

            if ($result->latestRelease) {
                $this->latestRelease = $result->latestRelease->toArray();
            }

            if ($result->error) {
                $this->error = $result->error;
            } elseif ($this->updateAvailable) {
                $this->addLog("Nueva versión disponible: v{$this->latestVersion}");
            } else {
                $this->addLog("Ya tienes la última versión (v{$this->currentVersion}).");
            }

        } catch (\Exception $e) {
            $this->error = 'Error al verificar actualizaciones: ' . $e->getMessage();
        }

        $this->isChecking = false;
    }

    public function downloadUpdate(): void
    {
        if (!$this->updateAvailable || !$this->latestRelease) {
            return;
        }

        $this->isDownloading = true;
        $this->error = null;

        try {
            $service = app(UpdateService::class);
            $result = $service->downloadUpdate(
                $this->latestRelease['version'],
                $this->latestRelease['download_url']
            );

            foreach ($result->logs as $log) {
                $this->addLog($log);
            }

            if (!$result->success) {
                $this->error = $result->message;
                foreach ($result->errors as $err) {
                    $this->addLog("ERROR: {$err}");
                }
            }

        } catch (\Exception $e) {
            $this->error = 'Error al descargar: ' . $e->getMessage();
        }

        $this->isDownloading = false;
    }

    public function createBackup(): void
    {
        $this->isBackingUp = true;
        $this->error = null;

        try {
            $service = app(UpdateService::class);
            $result = $service->createBackup();

            foreach ($result->logs as $log) {
                $this->addLog($log);
            }

            if (!$result->success) {
                $this->error = $result->message;
                foreach ($result->errors as $err) {
                    $this->addLog("ERROR: {$err}");
                }
            } else {
                $this->backups = $service->getBackups();
            }

        } catch (\Exception $e) {
            $this->error = 'Error al crear backup: ' . $e->getMessage();
        }

        $this->isBackingUp = false;
    }

    public function applyUpdate(): void
    {
        if (!$this->updateAvailable || !$this->latestRelease) {
            return;
        }

        $this->isUpdating = true;
        $this->error = null;

        try {
            $service = app(UpdateService::class);

            // First check if update is downloaded
            $updatePath = $service->getUpdateFilePath($this->latestRelease['version']);

            if (!$updatePath) {
                $this->addLog("Descargando actualización...");
                $downloadResult = $service->downloadUpdate(
                    $this->latestRelease['version'],
                    $this->latestRelease['download_url']
                );

                foreach ($downloadResult->logs as $log) {
                    $this->addLog($log);
                }

                if (!$downloadResult->success) {
                    $this->error = $downloadResult->message;
                    $this->isUpdating = false;
                    return;
                }

                $updatePath = $service->getUpdateFilePath($this->latestRelease['version']);
            }

            // Create backup first
            $this->addLog("Creando backup de seguridad...");
            $backupResult = $service->createBackup();

            foreach ($backupResult->logs as $log) {
                $this->addLog($log);
            }

            if (!$backupResult->success) {
                $this->error = 'Error al crear backup: ' . $backupResult->message;
                $this->isUpdating = false;
                return;
            }

            // Apply update
            $this->addLog("Aplicando actualización...");
            $applyResult = $service->applyUpdate(
                $updatePath,
                $this->latestRelease['version'],
                $backupResult->backupPath
            );

            foreach ($applyResult->logs as $log) {
                $this->addLog($log);
            }

            if (!$applyResult->success) {
                $this->error = $applyResult->message;
                foreach ($applyResult->errors as $err) {
                    $this->addLog("ERROR: {$err}");
                }
            } else {
                $this->currentVersion = $this->latestRelease['version'];
                $this->updateAvailable = false;
                $this->latestRelease = null;
                $this->addLog("¡Actualización completada!");

                // Refresh backups list
                $this->backups = $service->getBackups();
            }

        } catch (\Exception $e) {
            $this->error = 'Error al aplicar actualización: ' . $e->getMessage();
            $this->addLog("ERROR: " . $e->getMessage());
        }

        $this->isUpdating = false;
    }

    public function performFullUpdate(): void
    {
        if (!$this->updateAvailable || !$this->latestRelease) {
            return;
        }

        $this->isUpdating = true;
        $this->error = null;
        $this->logs = [];

        try {
            $service = app(UpdateService::class);

            $this->addLog("Iniciando proceso de actualización completo...");

            $result = $service->performFullUpdate(
                $this->latestRelease['version'],
                $this->latestRelease['download_url']
            );

            foreach ($result->logs as $log) {
                $this->addLog($log);
            }

            if (!$result->success) {
                $this->error = $result->message;
                foreach ($result->errors as $err) {
                    $this->addLog("ERROR: {$err}");
                }
            } else {
                $this->currentVersion = $this->latestRelease['version'];
                $this->updateAvailable = false;
                $this->latestRelease = null;

                // Refresh backups list
                $this->backups = $service->getBackups();
            }

        } catch (\Exception $e) {
            $this->error = 'Error durante actualización: ' . $e->getMessage();
            $this->addLog("ERROR: " . $e->getMessage());
        }

        $this->isUpdating = false;
    }

    public function clearLogs(): void
    {
        $this->logs = [];
    }

    private function addLog(string $message): void
    {
        $timestamp = now()->format('H:i:s');
        $this->logs[] = "[{$timestamp}] {$message}";
    }

    public function render()
    {
        return view('livewire.updates.panel')
            ->layout('components.layouts.app', ['title' => 'Actualizaciones']);
    }
}
