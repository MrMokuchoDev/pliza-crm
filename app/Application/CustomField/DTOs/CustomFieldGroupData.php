<?php

declare(strict_types=1);

namespace App\Application\CustomField\DTOs;

use App\Domain\CustomField\Entities\CustomFieldGroup;

final class CustomFieldGroupData
{
    public function __construct(
        public readonly string $id,
        public readonly string $entityType,
        public readonly string $name,
        public readonly int $order,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    public static function fromEntity(CustomFieldGroup $group): self
    {
        return new self(
            id: $group->id()->toString(),
            entityType: $group->entityType()->value,
            name: $group->name(),
            order: $group->order(),
            createdAt: $group->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $group->updatedAt()->format('Y-m-d H:i:s'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            entityType: $data['entity_type'],
            name: $data['name'],
            order: $data['order'],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'entity_type' => $this->entityType,
            'name' => $this->name,
            'order' => $this->order,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
