<?php

declare(strict_types=1);

namespace App\Domain\CustomField\Entities;

use App\Domain\CustomField\ValueObjects\EntityType;
use Ramsey\Uuid\UuidInterface;

final class CustomFieldValue
{
    public function __construct(
        private readonly UuidInterface $id,
        private readonly UuidInterface $customFieldId,
        private EntityType $entityType,
        private readonly UuidInterface $entityId,
        private ?string $value,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        UuidInterface $id,
        UuidInterface $customFieldId,
        EntityType $entityType,
        UuidInterface $entityId,
        ?string $value
    ): self {
        return new self(
            id: $id,
            customFieldId: $customFieldId,
            entityType: $entityType,
            entityId: $entityId,
            value: $value,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    // Getters
    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function customFieldId(): UuidInterface
    {
        return $this->customFieldId;
    }

    public function entityType(): EntityType
    {
        return $this->entityType;
    }

    public function entityId(): UuidInterface
    {
        return $this->entityId;
    }

    public function value(): ?string
    {
        return $this->value;
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
    public function changeValue(?string $value): void
    {
        $this->value = $value;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Decodificar valor como array (para multiselect/checkbox)
     */
    public function getAsArray(): ?array
    {
        if ($this->value === null) {
            return null;
        }

        $decoded = json_decode($this->value, true);
        return is_array($decoded) ? $decoded : [$this->value];
    }

    /**
     * Codificar valor desde array
     */
    public static function encodeArray(array $values): string
    {
        return json_encode($values);
    }
}
