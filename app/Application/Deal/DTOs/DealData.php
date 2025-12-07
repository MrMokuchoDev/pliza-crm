<?php

declare(strict_types=1);

namespace App\Application\Deal\DTOs;

/**
 * DTO para transferir datos de Deal entre capas.
 */
readonly class DealData
{
    public function __construct(
        public ?string $id = null,
        public ?string $leadId = null,
        public ?string $name = null,
        public ?float $value = null,
        public ?string $salePhaseId = null,
        public ?string $description = null,
        public ?string $estimatedCloseDate = null,
        public ?string $closeDate = null,
    ) {}

    /**
     * Crear desde array (útil para formularios).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            leadId: $data['lead_id'] ?? null,
            name: $data['name'] ?? null,
            value: isset($data['value']) ? (float) $data['value'] : null,
            salePhaseId: $data['sale_phase_id'] ?? null,
            description: $data['description'] ?? null,
            estimatedCloseDate: $data['estimated_close_date'] ?? null,
            closeDate: $data['close_date'] ?? null,
        );
    }

    /**
     * Convertir a array para persistencia.
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'lead_id' => $this->leadId,
            'name' => $this->name,
            'value' => $this->value,
            'sale_phase_id' => $this->salePhaseId,
            'description' => $this->description,
            'estimated_close_date' => $this->estimatedCloseDate,
            'close_date' => $this->closeDate,
        ], fn ($value) => $value !== null);
    }
}
