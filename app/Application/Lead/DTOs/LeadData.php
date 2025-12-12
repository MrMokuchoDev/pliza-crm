<?php

declare(strict_types=1);

namespace App\Application\Lead\DTOs;

use App\Domain\Lead\ValueObjects\SourceType;

/**
 * DTO para transferir datos de Lead entre capas.
 */
readonly class LeadData
{
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $message = null,
        public ?SourceType $sourceType = null,
        public ?string $sourceSiteId = null,
        public ?string $sourceUrl = null,
        public ?array $metadata = null,
        public ?string $assignedTo = null,
    ) {}

    /**
     * Crear desde array (útil para formularios).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            message: $data['message'] ?? null,
            sourceType: isset($data['source_type'])
                ? (is_string($data['source_type']) ? SourceType::tryFrom($data['source_type']) : $data['source_type'])
                : null,
            sourceSiteId: $data['source_site_id'] ?? null,
            sourceUrl: $data['source_url'] ?? null,
            metadata: $data['metadata'] ?? null,
            assignedTo: $data['assigned_to'] ?? null,
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
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'source_type' => $this->sourceType?->value,
            'source_site_id' => $this->sourceSiteId,
            'source_url' => $this->sourceUrl,
            'metadata' => $this->metadata,
            'assigned_to' => $this->assignedTo,
        ], fn ($value) => $value !== null);
    }
}
