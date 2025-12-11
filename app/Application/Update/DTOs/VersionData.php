<?php

declare(strict_types=1);

namespace App\Application\Update\DTOs;

class VersionData
{
    public function __construct(
        public readonly string $version,
        public readonly string $tagName,
        public readonly string $name,
        public readonly string $changelog,
        public readonly ?string $downloadUrl,
        public readonly ?int $downloadSize,
        public readonly string $publishedAt,
        public readonly bool $isPrerelease,
        public readonly array $rawData = [],
    ) {}

    /**
     * Create from GitHub release API response.
     */
    public static function fromGitHubRelease(array $release): self
    {
        $downloadUrl = null;
        $downloadSize = null;

        // Try to get pre-built ZIP from assets first
        if (!empty($release['assets'])) {
            foreach ($release['assets'] as $asset) {
                if (str_ends_with($asset['name'], '.zip')) {
                    $downloadUrl = $asset['browser_download_url'];
                    $downloadSize = $asset['size'] ?? null;
                    break;
                }
            }
        }

        // Fall back to source code ZIP
        if (!$downloadUrl) {
            $downloadUrl = $release['zipball_url'] ?? null;
        }

        return new self(
            version: ltrim($release['tag_name'] ?? '', 'vV'),
            tagName: $release['tag_name'] ?? '',
            name: $release['name'] ?? $release['tag_name'] ?? '',
            changelog: $release['body'] ?? '',
            downloadUrl: $downloadUrl,
            downloadSize: $downloadSize,
            publishedAt: $release['published_at'] ?? $release['created_at'] ?? '',
            isPrerelease: $release['prerelease'] ?? false,
            rawData: $release,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'tag_name' => $this->tagName,
            'name' => $this->name,
            'changelog' => $this->changelog,
            'download_url' => $this->downloadUrl,
            'download_size' => $this->downloadSize,
            'published_at' => $this->publishedAt,
            'is_prerelease' => $this->isPrerelease,
        ];
    }

    /**
     * Get human-readable download size.
     */
    public function getFormattedSize(): string
    {
        if (!$this->downloadSize) {
            return 'Desconocido';
        }

        $bytes = $this->downloadSize;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get formatted publish date.
     */
    public function getFormattedDate(): string
    {
        if (!$this->publishedAt) {
            return 'Desconocido';
        }

        return \Carbon\Carbon::parse($this->publishedAt)->format('d/m/Y H:i');
    }
}
