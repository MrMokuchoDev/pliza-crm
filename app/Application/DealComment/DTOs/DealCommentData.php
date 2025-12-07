<?php

declare(strict_types=1);

namespace App\Application\DealComment\DTOs;

/**
 * DTO para transferir datos de DealComment entre capas.
 */
readonly class DealCommentData
{
    public function __construct(
        public ?string $id = null,
        public ?string $dealId = null,
        public ?string $content = null,
        public ?string $type = null,
    ) {}

    /**
     * Crear desde array (útil para formularios).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            dealId: $data['deal_id'] ?? null,
            content: $data['content'] ?? null,
            type: $data['type'] ?? 'general',
        );
    }

    /**
     * Convertir a array para persistencia.
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'deal_id' => $this->dealId,
            'content' => $this->content,
            'type' => $this->type,
        ], fn ($value) => $value !== null);
    }
}
