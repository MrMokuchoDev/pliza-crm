<?php

declare(strict_types=1);

namespace App\Domain\Deal\Entities;

/**
 * Entidad de dominio para negocios/deals.
 */
final class Deal
{
    public function __construct(
        public readonly string $id,
        public readonly string $leadId,
        public readonly string $salePhaseId,
        public readonly string $name,
        public readonly ?float $value,
        public readonly ?string $description,
        public readonly ?\DateTimeImmutable $estimatedCloseDate,
        public readonly ?\DateTimeImmutable $closeDate,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
        public readonly ?\DateTimeImmutable $deletedAt = null,
    ) {}

    public static function create(
        string $id,
        string $leadId,
        string $salePhaseId,
        string $name,
        ?float $value = null,
        ?string $description = null,
        ?\DateTimeImmutable $estimatedCloseDate = null,
    ): self {
        return new self(
            id: $id,
            leadId: $leadId,
            salePhaseId: $salePhaseId,
            name: $name,
            value: $value,
            description: $description,
            estimatedCloseDate: $estimatedCloseDate,
            closeDate: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function isClosed(): bool
    {
        return $this->closeDate !== null;
    }

    public function getFormattedValue(): ?string
    {
        if ($this->value === null) {
            return null;
        }

        return number_format($this->value, 2, ',', '.');
    }
}
