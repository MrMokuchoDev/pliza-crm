<?php

declare(strict_types=1);

namespace App\Application\Site\DTOs;

/**
 * DTO para transferir datos de Site entre capas.
 */
readonly class SiteData
{
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $domain = null,
        public ?string $apiKey = null,
        public ?bool $isActive = null,
        public ?array $settings = null,
    ) {}

    /**
     * Crear desde array (útil para formularios).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            domain: $data['domain'] ?? null,
            apiKey: $data['api_key'] ?? null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null,
            settings: $data['settings'] ?? null,
        );
    }

    /**
     * Convertir a array para persistencia.
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'domain' => $this->domain,
            'api_key' => $this->apiKey,
            'is_active' => $this->isActive,
            'settings' => $this->settings,
        ], fn ($value) => $value !== null);
    }
}
