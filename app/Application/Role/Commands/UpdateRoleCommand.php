<?php

declare(strict_types=1);

namespace App\Application\Role\Commands;

final readonly class UpdateRoleCommand
{
    public function __construct(
        public string $id,
        public string $name,
        public string $displayName,
        public ?string $description,
        public int $level,
    ) {}
}
