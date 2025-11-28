<?php

declare(strict_types=1);

namespace App\Domain\SalePhase\Entities;

/**
 * Entidad de dominio para fases de venta.
 */
final class SalePhase
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $order,
        public readonly string $color,
        public readonly bool $isClosed,
        public readonly bool $isWon,
        public readonly bool $isDefault,
        public readonly ?\DateTimeImmutable $createdAt = null,
    ) {}

    public static function create(
        string $id,
        string $name,
        int $order,
        string $color = '#6B7280',
        bool $isClosed = false,
        bool $isWon = false,
        bool $isDefault = false,
    ): self {
        return new self(
            id: $id,
            name: $name,
            order: $order,
            color: $color,
            isClosed: $isClosed,
            isWon: $isWon,
            isDefault: $isDefault,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public function isClosedPhase(): bool
    {
        return $this->isClosed;
    }

    public function isWonPhase(): bool
    {
        return $this->isClosed && $this->isWon;
    }

    public function isLostPhase(): bool
    {
        return $this->isClosed && !$this->isWon;
    }
}
