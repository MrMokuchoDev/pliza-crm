<?php

declare(strict_types=1);

namespace App\Application\Role\Commands;

final readonly class DeleteRoleCommand
{
    public function __construct(
        public string $id,
    ) {}
}
