<?php

declare(strict_types=1);

namespace App\Application\Role\DTOs;

final readonly class RoleDTO
{
    public function __construct(
        public ?string $id,
        public string $name,
        public string $displayName,
        public ?string $description,
        public int $level,
        public array $permissionIds = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            displayName: $data['display_name'] ?? ucfirst(str_replace('_', ' ', $data['name'])),
            description: $data['description'] ?? null,
            level: (int) ($data['level'] ?? 10),
            permissionIds: $data['permission_ids'] ?? [],
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
            'description' => $this->description,
            'level' => $this->level,
            'permission_ids' => $this->permissionIds,
        ];
    }
}
