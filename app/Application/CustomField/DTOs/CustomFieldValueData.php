<?php

declare(strict_types=1);

namespace App\Application\CustomField\DTOs;

use App\Domain\CustomField\Entities\CustomFieldValue;

final class CustomFieldValueData
{
    public function __construct(
        public readonly string $id,
        public readonly string $customFieldId,
        public readonly string $entityType,
        public readonly string $entityId,
        public readonly mixed $value,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?CustomFieldData $field = null,
    ) {}

    public static function fromEntity(CustomFieldValue $value, ?CustomFieldData $field = null): self
    {
        return new self(
            id: $value->id()->toString(),
            customFieldId: $value->customFieldId()->toString(),
            entityType: $value->entityType()->value,
            entityId: $value->entityId()->toString(),
            value: $value->value(),
            createdAt: $value->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $value->updatedAt()->format('Y-m-d H:i:s'),
            field: $field,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            customFieldId: $data['custom_field_id'],
            entityType: $data['entity_type'],
            entityId: $data['entity_id'],
            value: $data['value'],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
            field: isset($data['field']) ? CustomFieldData::fromArray($data['field']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'custom_field_id' => $this->customFieldId,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'value' => $this->value,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'field' => $this->field?->toArray(),
        ];
    }

    /**
     * Obtener valor decodificado para campos multi-valor
     */
    public function getDecodedValue(): mixed
    {
        if ($this->value === null) {
            return null;
        }

        // Intentar decodificar como JSON
        $decoded = json_decode($this->value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->value;
    }
}
