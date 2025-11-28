<?php

declare(strict_types=1);

namespace App\Domain\Site\Entities;

/**
 * Entidad de dominio para sitios web registrados.
 */
final class Site
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $domain,
        public readonly string $apiKey,
        public readonly bool $isActive,
        public readonly ?array $settings,
        public readonly ?\DateTimeImmutable $createdAt = null,
    ) {}

    public static function create(
        string $id,
        string $name,
        string $domain,
        string $apiKey,
        bool $isActive = true,
        ?array $settings = null,
    ): self {
        return new self(
            id: $id,
            name: $name,
            domain: $domain,
            apiKey: $apiKey,
            isActive: $isActive,
            settings: $settings,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public function isEnabled(): bool
    {
        return $this->isActive;
    }
}
