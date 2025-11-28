<?php

declare(strict_types=1);

namespace App\Domain\Note\Entities;

/**
 * Entidad de dominio para notas de leads.
 */
final class Note
{
    public function __construct(
        public readonly string $id,
        public readonly string $leadId,
        public readonly string $content,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function create(
        string $id,
        string $leadId,
        string $content,
    ): self {
        return new self(
            id: $id,
            leadId: $leadId,
            content: $content,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }
}
