<?php

declare(strict_types=1);

namespace App\Domain\CustomField\Entities;

use App\Domain\CustomField\ValueObjects\EntityType;
use Ramsey\Uuid\UuidInterface;

final class CustomFieldGroup
{
    public function __construct(
        private readonly UuidInterface $id,
        private EntityType $entityType,
        private string $name,
        private int $order,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        UuidInterface $id,
        EntityType $entityType,
        string $name,
        int $order
    ): self {
        return new self(
            id: $id,
            entityType: $entityType,
            name: $name,
            order: $order,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    // Getters
    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function entityType(): EntityType
    {
        return $this->entityType;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Business methods
    public function changeName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function changeOrder(int $order): void
    {
        $this->order = $order;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
