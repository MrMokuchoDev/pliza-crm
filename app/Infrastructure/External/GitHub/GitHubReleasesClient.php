<?php

declare(strict_types=1);

namespace App\Infrastructure\External\GitHub;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubReleasesClient
{
    private const API_BASE_URL = 'https://api.github.com';
    private const CACHE_TTL_MINUTES = 60;

    private string $repository;
    private string $channel;

    public function __construct()
    {
        $this->repository = config('version.github_repo');
        $this->channel = config('version.channel', 'stable');
    }

    /**
     * Get the latest release from GitHub.
     *
     * @return array|null Release data or null if not found
     */
    public function getLatestRelease(): ?array
    {
        $cacheKey = "github_releases_{$this->repository}_latest_{$this->channel}";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () {
            try {
                if ($this->channel === 'stable') {
                    return $this->fetchLatestStableRelease();
                }

                return $this->fetchLatestRelease();
            } catch (\Exception $e) {
                Log::error('GitHub API error: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Get all releases from GitHub.
     *
     * @param int $perPage Number of releases per page
     * @return array List of releases
     */
    public function getAllReleases(int $perPage = 10): array
    {
        $cacheKey = "github_releases_{$this->repository}_all_{$perPage}";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($perPage) {
            try {
                $response = Http::withHeaders($this->getHeaders())
                    ->get($this->getReleasesUrl(), ['per_page' => $perPage]);

                if ($response->failed()) {
                    Log::warning('GitHub API request failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return [];
                }

                $releases = $response->json();

                if ($this->channel === 'stable') {
                    $releases = array_filter($releases, fn($r) => !($r['prerelease'] ?? false));
                }

                return array_values($releases);
            } catch (\Exception $e) {
                Log::error('GitHub API error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get a specific release by tag.
     *
     * @param string $tag Tag name (e.g., 'v1.0.0')
     * @return array|null Release data or null if not found
     */
    public function getReleaseByTag(string $tag): ?array
    {
        $cacheKey = "github_releases_{$this->repository}_tag_{$tag}";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($tag) {
            try {
                $url = self::API_BASE_URL . "/repos/{$this->repository}/releases/tags/{$tag}";

                $response = Http::withHeaders($this->getHeaders())->get($url);

                if ($response->failed()) {
                    return null;
                }

                return $response->json();
            } catch (\Exception $e) {
                Log::error('GitHub API error: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Download release asset.
     *
     * @param string $downloadUrl The download URL for the asset
     * @param string $destinationPath Where to save the file
     * @return bool True if download succeeded
     */
    public function downloadAsset(string $downloadUrl, string $destinationPath): bool
    {
        try {
            // Use cURL directly for better redirect handling
            $ch = curl_init();

            // GitHub zipball URLs need specific Accept header
            $acceptHeader = str_contains($downloadUrl, 'zipball')
                ? 'Accept: application/vnd.github+json'
                : 'Accept: application/octet-stream';

            curl_setopt_array($ch, [
                CURLOPT_URL => $downloadUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_HTTPHEADER => [
                    $acceptHeader,
                    'User-Agent: Pliza-CRM-Updater',
                ],
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode !== 200 || $content === false) {
                Log::error('Failed to download asset', [
                    'url' => $downloadUrl,
                    'status' => $httpCode,
                    'error' => $error,
                ]);
                return false;
            }

            $directory = dirname($destinationPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($destinationPath, $content);

            return true;
        } catch (\Exception $e) {
            Log::error('Error downloading asset: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the ZIP download URL for a release.
     *
     * @param array $release Release data from API
     * @return string|null Download URL or null
     */
    public function getZipDownloadUrl(array $release): ?string
    {
        // First, try to find a pre-built ZIP in assets
        if (!empty($release['assets'])) {
            foreach ($release['assets'] as $asset) {
                if (str_ends_with($asset['name'], '.zip')) {
                    return $asset['browser_download_url'];
                }
            }
        }

        // Fall back to the source code ZIP
        return $release['zipball_url'] ?? null;
    }

    /**
     * Parse version from tag name.
     *
     * @param string $tagName Tag name (e.g., 'v1.0.0')
     * @return string Version without 'v' prefix
     */
    public function parseVersion(string $tagName): string
    {
        return ltrim($tagName, 'vV');
    }

    /**
     * Clear the releases cache.
     */
    public function clearCache(): void
    {
        Cache::forget("github_releases_{$this->repository}_latest_{$this->channel}");
        Cache::forget("github_releases_{$this->repository}_all_10");
    }

    /**
     * Fetch the latest stable release (non-prerelease).
     */
    private function fetchLatestStableRelease(): ?array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->getReleasesUrl(), ['per_page' => 20]);

        if ($response->failed()) {
            return null;
        }

        $releases = $response->json();

        foreach ($releases as $release) {
            if (!($release['prerelease'] ?? false) && !($release['draft'] ?? false)) {
                return $release;
            }
        }

        return null;
    }

    /**
     * Fetch the latest release (including prereleases).
     */
    private function fetchLatestRelease(): ?array
    {
        $url = self::API_BASE_URL . "/repos/{$this->repository}/releases/latest";

        $response = Http::withHeaders($this->getHeaders())->get($url);

        if ($response->failed()) {
            // Try fetching all releases if /latest fails
            $allReleases = $this->getAllReleases(1);
            return $allReleases[0] ?? null;
        }

        return $response->json();
    }

    /**
     * Get the releases API URL.
     */
    private function getReleasesUrl(): string
    {
        return self::API_BASE_URL . "/repos/{$this->repository}/releases";
    }

    /**
     * Get HTTP headers for API requests.
     */
    private function getHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'Pliza-CRM-Updater',
        ];
    }
}
