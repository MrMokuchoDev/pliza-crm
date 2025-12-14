<?php

declare(strict_types=1);

namespace App\Application\Role\DTOs;

final readonly class PermissionDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $displayName,
        public string $group,
        public ?string $description,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            displayName: $data['display_name'],
            group: $data['group'],
            description: $data['description'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->displayName,
            'group' => $this->group,
            'description' => $this->description,
        ];
    }
}
