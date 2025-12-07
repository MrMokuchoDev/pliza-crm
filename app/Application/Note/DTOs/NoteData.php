<?php

declare(strict_types=1);

namespace App\Application\Note\DTOs;

/**
 * DTO para transferir datos de Note entre capas.
 */
readonly class NoteData
{
    public function __construct(
        public ?string $id = null,
        public ?string $leadId = null,
        public ?string $content = null,
    ) {}

    /**
     * Crear desde array (útil para formularios).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            leadId: $data['lead_id'] ?? null,
            content: $data['content'] ?? null,
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
            'content' => $this->content,
        ], fn ($value) => $value !== null);
    }
}
