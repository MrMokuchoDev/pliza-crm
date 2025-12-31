<?php

declare(strict_types=1);

namespace App\Application\CustomField\DTOs;

use App\Domain\CustomField\Entities\CustomField;

final class CustomFieldData
{
    public function __construct(
        public readonly string $id,
        public readonly string $entityType,
        public readonly ?string $groupId,
        public readonly string $name,
        public readonly string $label,
        public readonly string $type,
        public readonly ?string $defaultValue,
        public readonly bool $isRequired,
        public readonly ?array $validationRules,
        public readonly int $order,
        public readonly bool $isActive,
        public readonly bool $isSystem,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?CustomFieldGroupData $group = null,
    ) {}

    public static function fromEntity(CustomField $field, ?CustomFieldGroupData $group = null): self
    {
        return new self(
            id: $field->id()->toString(),
            entityType: $field->entityType()->value,
            groupId: $field->groupId()?->toString(),
            name: $field->name()->value(),
            label: $field->label(),
            type: $field->type()->value,
            defaultValue: $field->defaultValue(),
            isRequired: $field->isRequired(),
            validationRules: $field->validationRules()->toArray(),
            order: $field->order(),
            isActive: $field->isActive(),
            isSystem: $field->isSystem(),
            createdAt: $field->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $field->updatedAt()->format('Y-m-d H:i:s'),
            group: $group,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            entityType: $data['entity_type'],
            groupId: $data['group_id'] ?? null,
            name: $data['name'],
            label: $data['label'],
            type: $data['type'],
            defaultValue: $data['default_value'] ?? null,
            isRequired: $data['is_required'],
            validationRules: $data['validation_rules'] ?? null,
            order: $data['order'],
            isActive: $data['is_active'],
            isSystem: $data['is_system'] ?? false,
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
            group: isset($data['group']) ? CustomFieldGroupData::fromArray($data['group']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'entity_type' => $this->entityType,
            'group_id' => $this->groupId,
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'default_value' => $this->defaultValue,
            'is_required' => $this->isRequired,
            'validation_rules' => $this->validationRules,
            'order' => $this->order,
            'is_active' => $this->isActive,
            'is_system' => $this->isSystem,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'group' => $this->group?->toArray(),
        ];
    }
}
