<?php

declare(strict_types=1);

namespace App\Application\SalePhase\DTOs;

/**
 * DTO para transferir datos de SalePhase entre capas.
 */
readonly class SalePhaseData
{
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?int $order = null,
        public ?string $color = null,
        public ?bool $isClosed = null,
        public ?bool $isWon = null,
        public ?bool $isDefault = null,
    ) {}

    /**
     * Crear desde array (útil para formularios).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            order: isset($data['order']) ? (int) $data['order'] : null,
            color: $data['color'] ?? null,
            isClosed: isset($data['is_closed']) ? (bool) $data['is_closed'] : null,
            isWon: isset($data['is_won']) ? (bool) $data['is_won'] : null,
            isDefault: isset($data['is_default']) ? (bool) $data['is_default'] : null,
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
            'order' => $this->order,
            'color' => $this->color,
            'is_closed' => $this->isClosed,
            'is_won' => $this->isWon,
            'is_default' => $this->isDefault,
        ], fn ($value) => $value !== null);
    }
}
