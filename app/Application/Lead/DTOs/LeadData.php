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
        public ?SourceType $sourceType = null,
        public ?string $sourceSiteId = null,
        public ?string $sourceUrl = null,
        public ?array $metadata = null,
        public ?string $assignedTo = null,
        public array $customFields = [],
    ) {}

    /**
     * Crear desde array (útil para formularios).
     */
    public static function fromArray(array $data): self
    {
        // Separar campos del sistema de custom fields
        $systemFields = ['id', 'source_type', 'source_site_id', 'source_url', 'metadata', 'assigned_to'];
        $customFields = [];

        foreach ($data as $key => $value) {
            if (!in_array($key, $systemFields)) {
                $customFields[$key] = $value;
            }
        }

        return new self(
            id: $data['id'] ?? null,
            sourceType: isset($data['source_type'])
                ? (is_string($data['source_type']) ? SourceType::tryFrom($data['source_type']) : $data['source_type'])
                : null,
            sourceSiteId: $data['source_site_id'] ?? null,
            sourceUrl: $data['source_url'] ?? null,
            metadata: $data['metadata'] ?? null,
            assignedTo: $data['assigned_to'] ?? null,
            customFields: $customFields,
        );
    }

    /**
     * Convertir a array para persistencia.
     */
    public function toArray(): array
    {
        $systemData = array_filter([
            'id' => $this->id,
            'source_type' => $this->sourceType?->value,
            'source_site_id' => $this->sourceSiteId,
            'source_url' => $this->sourceUrl,
            'metadata' => $this->metadata,
            'assigned_to' => $this->assignedTo,
        ], fn ($value) => $value !== null);

        return array_merge($systemData, $this->customFields);
    }
}
