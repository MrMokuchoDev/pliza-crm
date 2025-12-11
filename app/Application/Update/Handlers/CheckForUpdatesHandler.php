<?php

declare(strict_types=1);

namespace App\Application\Update\Handlers;

use App\Application\Update\Commands\CheckForUpdatesCommand;
use App\Application\Update\DTOs\UpdateCheckResult;
use App\Application\Update\DTOs\VersionData;
use App\Infrastructure\External\GitHub\GitHubReleasesClient;
use Illuminate\Support\Facades\Log;

class CheckForUpdatesHandler
{
    public function __construct(
        private readonly GitHubReleasesClient $githubClient,
    ) {}

    /**
     * Handle the check for updates command.
     */
    public function handle(CheckForUpdatesCommand $command): UpdateCheckResult
    {
        $currentVersion = config('version.current', '1.0.0');

        try {
            if ($command->forceRefresh) {
                $this->githubClient->clearCache();
            }

            $latestRelease = $this->githubClient->getLatestRelease();

            if (!$latestRelease) {
                return UpdateCheckResult::error(
                    $currentVersion,
                    'No se pudo obtener información de versiones desde GitHub.'
                );
            }

            $versionData = VersionData::fromGitHubRelease($latestRelease);
            $lastChecked = now();

            // Save last check timestamp
            $this->saveLastCheckTimestamp($lastChecked);

            // Compare versions
            if (version_compare($versionData->version, $currentVersion, '>')) {
                Log::info('Update available', [
                    'current' => $currentVersion,
                    'latest' => $versionData->version,
                ]);

                return UpdateCheckResult::available(
                    $currentVersion,
                    $versionData,
                    $lastChecked,
                );
            }

            return UpdateCheckResult::upToDate($currentVersion, $lastChecked);

        } catch (\Exception $e) {
            Log::error('Error checking for updates: ' . $e->getMessage());

            return UpdateCheckResult::error(
                $currentVersion,
                'Error al verificar actualizaciones: ' . $e->getMessage()
            );
        }
    }

    /**
     * Save the last check timestamp to cache file.
     */
    private function saveLastCheckTimestamp(\DateTimeInterface $timestamp): void
    {
        $cachePath = storage_path('version-cache.json');

        $data = [];
        if (file_exists($cachePath)) {
            $data = json_decode(file_get_contents($cachePath), true) ?? [];
        }

        $data['last_checked'] = $timestamp->format('Y-m-d H:i:s');

        file_put_contents($cachePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Get the last check timestamp from cache file.
     */
    public function getLastCheckTimestamp(): ?\DateTimeInterface
    {
        $cachePath = storage_path('version-cache.json');

        if (!file_exists($cachePath)) {
            return null;
        }

        $data = json_decode(file_get_contents($cachePath), true);

        if (!isset($data['last_checked'])) {
            return null;
        }

        return \Carbon\Carbon::parse($data['last_checked']);
    }
}
