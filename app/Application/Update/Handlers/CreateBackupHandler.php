<?php

declare(strict_types=1);

namespace App\Application\Update\Handlers;

use App\Application\Update\Commands\CreateBackupCommand;
use App\Application\Update\DTOs\UpdateResult;
use Illuminate\Support\Facades\Log;

class CreateBackupHandler
{
    /**
     * Handle the create backup command.
     */
    public function handle(CreateBackupCommand $command): UpdateResult
    {
        $logs = [];
        $timestamp = date('Y-m-d_His');
        $backupDir = storage_path("app/backups/{$timestamp}");

        try {
            $logs[] = "Iniciando backup...";

            // Create backup directory
            if (!mkdir($backupDir, 0755, true)) {
                return UpdateResult::failure(
                    'No se pudo crear el directorio de backup.',
                    ['Error al crear: ' . $backupDir],
                    $logs,
                );
            }

            $logs[] = "Directorio de backup creado: {$timestamp}";

            // Backup .env file
            $envPath = base_path('.env');
            if (file_exists($envPath)) {
                copy($envPath, $backupDir . '/.env');
                $logs[] = "Archivo .env respaldado.";
            }

            // Backup config/version.php
            $versionPath = config_path('version.php');
            if (file_exists($versionPath)) {
                if (!is_dir($backupDir . '/config')) {
                    mkdir($backupDir . '/config', 0755, true);
                }
                copy($versionPath, $backupDir . '/config/version.php');
                $logs[] = "Archivo version.php respaldado.";
            }

            // Backup uploads if requested
            if ($command->includeUploads) {
                $uploadsDir = storage_path('app/public');
                if (is_dir($uploadsDir)) {
                    $this->copyDirectory($uploadsDir, $backupDir . '/uploads');
                    $logs[] = "Archivos de uploads respaldados.";
                }
            }

            // Backup database if requested
            if ($command->includeDatabase) {
                $dbBackupResult = $this->backupDatabase($backupDir);
                if ($dbBackupResult['success']) {
                    $logs[] = $dbBackupResult['message'];
                } else {
                    $logs[] = "Advertencia: " . $dbBackupResult['message'];
                }
            }

            // Cleanup old backups
            $this->cleanupOldBackups();

            $logs[] = "Backup completado exitosamente.";

            Log::info('Backup created', ['path' => $backupDir]);

            return UpdateResult::success(
                'Backup creado exitosamente.',
                logs: $logs,
                backupPath: $backupDir,
            );

        } catch (\Exception $e) {
            Log::error('Error creating backup: ' . $e->getMessage());

            // Try to cleanup partial backup
            if (is_dir($backupDir)) {
                $this->deleteDirectory($backupDir);
            }

            return UpdateResult::failure(
                'Error al crear el backup.',
                [$e->getMessage()],
                $logs,
            );
        }
    }

    /**
     * Backup the database using mysqldump if available.
     */
    private function backupDatabase(string $backupDir): array
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if ($config['driver'] !== 'mysql') {
            return [
                'success' => false,
                'message' => 'Backup de BD solo disponible para MySQL.',
            ];
        }

        // Check if mysqldump is available
        $mysqldumpPath = $this->findMysqldump();
        if (!$mysqldumpPath) {
            // Try PHP-based backup as fallback
            return $this->backupDatabasePHP($backupDir, $config);
        }

        $host = $config['host'];
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $backupFile = $backupDir . '/database.sql';

        // Build mysqldump command
        $command = sprintf(
            '%s --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg($mysqldumpPath),
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($backupFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($backupFile) || filesize($backupFile) === 0) {
            // Fallback to PHP-based backup
            return $this->backupDatabasePHP($backupDir, $config);
        }

        return [
            'success' => true,
            'message' => 'Base de datos respaldada via mysqldump.',
        ];
    }

    /**
     * Find mysqldump executable.
     */
    private function findMysqldump(): ?string
    {
        $paths = [
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/local/mysql/bin/mysqldump',
            'mysqldump', // System PATH
        ];

        foreach ($paths as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        // Check if available in PATH
        $which = shell_exec('which mysqldump 2>/dev/null');
        if ($which && is_executable(trim($which))) {
            return trim($which);
        }

        return null;
    }

    /**
     * PHP-based database backup (fallback when mysqldump not available).
     */
    private function backupDatabasePHP(string $backupDir, array $config): array
    {
        try {
            $pdo = new \PDO(
                "mysql:host={$config['host']};dbname={$config['database']}",
                $config['username'],
                $config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            $backupFile = $backupDir . '/database.sql';
            $output = [];

            // Get all tables
            $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                // Table structure
                $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                $output[] = "DROP TABLE IF EXISTS `{$table}`;";
                $output[] = $createTable['Create Table'] . ";";
                $output[] = "";

                // Table data
                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);

                if (!empty($rows)) {
                    $columns = array_keys($rows[0]);
                    $columnList = '`' . implode('`, `', $columns) . '`';

                    foreach ($rows as $row) {
                        $values = array_map(function ($value) use ($pdo) {
                            if ($value === null) {
                                return 'NULL';
                            }
                            return $pdo->quote($value);
                        }, array_values($row));

                        $output[] = "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");";
                    }
                    $output[] = "";
                }
            }

            file_put_contents($backupFile, implode("\n", $output));

            return [
                'success' => true,
                'message' => 'Base de datos respaldada via PHP.',
            ];

        } catch (\Exception $e) {
            Log::warning('PHP database backup failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'No se pudo respaldar la BD: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Copy a directory recursively.
     */
    private function copyDirectory(string $source, string $dest): void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $dir = opendir($source);

        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $srcPath = $source . '/' . $file;
            $destPath = $dest . '/' . $file;

            if (is_dir($srcPath)) {
                $this->copyDirectory($srcPath, $destPath);
            } else {
                copy($srcPath, $destPath);
            }
        }

        closedir($dir);
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

    /**
     * Cleanup old backups, keeping only the most recent ones.
     */
    private function cleanupOldBackups(): void
    {
        $maxBackups = config('version.backup.max_backups', 3);
        $backupsDir = storage_path('app/backups');

        if (!is_dir($backupsDir)) {
            return;
        }

        $dirs = glob($backupsDir . '/*', GLOB_ONLYDIR);

        if (count($dirs) <= $maxBackups) {
            return;
        }

        // Sort by name (timestamp) descending
        rsort($dirs);

        // Remove old backups
        foreach (array_slice($dirs, $maxBackups) as $dir) {
            $this->deleteDirectory($dir);
        }
    }

    /**
     * Get list of available backups.
     */
    public function getBackups(): array
    {
        $backupsDir = storage_path('app/backups');

        if (!is_dir($backupsDir)) {
            return [];
        }

        $dirs = glob($backupsDir . '/*', GLOB_ONLYDIR);
        $backups = [];

        foreach ($dirs as $dir) {
            $name = basename($dir);
            $backups[] = [
                'name' => $name,
                'path' => $dir,
                'date' => \Carbon\Carbon::createFromFormat('Y-m-d_His', $name)->format('d/m/Y H:i:s'),
                'size' => $this->getDirectorySize($dir),
            ];
        }

        // Sort by name descending (newest first)
        usort($backups, fn($a, $b) => strcmp($b['name'], $a['name']));

        return $backups;
    }

    /**
     * Get total size of a directory.
     */
    private function getDirectorySize(string $dir): string
    {
        $size = 0;

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
}
