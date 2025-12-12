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
        public ?string $defaultUserId = null,
        public bool $clearDefaultUser = false,
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
            defaultUserId: $data['default_user_id'] ?? null,
            clearDefaultUser: $data['clear_default_user'] ?? false,
            settings: $data['settings'] ?? null,
        );
    }

    /**
     * Convertir a array para persistencia.
     */
    public function toArray(): array
    {
        $data = array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'domain' => $this->domain,
            'api_key' => $this->apiKey,
            'is_active' => $this->isActive,
            'settings' => $this->settings,
        ], fn ($value) => $value !== null);

        // Para default_user_id, incluir explícitamente (puede ser null para quitar el usuario)
        if ($this->defaultUserId !== null) {
            $data['default_user_id'] = $this->defaultUserId;
        } elseif ($this->clearDefaultUser) {
            $data['default_user_id'] = null;
        }

        return $data;
    }
}
