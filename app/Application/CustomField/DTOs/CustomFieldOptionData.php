<?php

declare(strict_types=1);

namespace App\Application\CustomField\DTOs;

final class CustomFieldOptionData
{
    public function __construct(
        public readonly string $id,
        public readonly string $customFieldId,
        public readonly string $label,
        public readonly string $value,
        public readonly int $order,
    ) {}

    public static function fromArray(array $data, string $customFieldId): self
    {
        return new self(
            id: $data['id'],
            customFieldId: $customFieldId,
            label: $data['label'],
            value: $data['value'],
            order: $data['order'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'custom_field_id' => $this->customFieldId,
            'label' => $this->label,
            'value' => $this->value,
            'order' => $this->order,
        ];
    }
}
