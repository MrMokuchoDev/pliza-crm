<?php

declare(strict_types=1);

namespace App\Application\Update\DTOs;

class UpdateCheckResult
{
    public function __construct(
        public readonly string $currentVersion,
        public readonly ?string $latestVersion,
        public readonly bool $updateAvailable,
        public readonly ?VersionData $latestRelease,
        public readonly ?\DateTimeInterface $lastChecked,
        public readonly ?string $error = null,
    ) {}

    /**
     * Create when update is available.
     */
    public static function available(
        string $currentVersion,
        VersionData $latestRelease,
        \DateTimeInterface $lastChecked,
    ): self {
        return new self(
            currentVersion: $currentVersion,
            latestVersion: $latestRelease->version,
            updateAvailable: true,
            latestRelease: $latestRelease,
            lastChecked: $lastChecked,
        );
    }

    /**
     * Create when already up to date.
     */
    public static function upToDate(
        string $currentVersion,
        \DateTimeInterface $lastChecked,
    ): self {
        return new self(
            currentVersion: $currentVersion,
            latestVersion: $currentVersion,
            updateAvailable: false,
            latestRelease: null,
            lastChecked: $lastChecked,
        );
    }

    /**
     * Create when check failed.
     */
    public static function error(string $currentVersion, string $error): self
    {
        return new self(
            currentVersion: $currentVersion,
            latestVersion: null,
            updateAvailable: false,
            latestRelease: null,
            lastChecked: null,
            error: $error,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'current_version' => $this->currentVersion,
            'latest_version' => $this->latestVersion,
            'update_available' => $this->updateAvailable,
            'latest_release' => $this->latestRelease?->toArray(),
            'last_checked' => $this->lastChecked?->format('Y-m-d H:i:s'),
            'error' => $this->error,
        ];
    }
}
