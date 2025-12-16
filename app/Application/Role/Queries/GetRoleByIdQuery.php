<?php

declare(strict_types=1);

namespace App\Application\Role\Queries;

final readonly class GetRoleByIdQuery
{
    public function __construct(
        public string $id,
        public bool $withPermissions = false,
    ) {}
}
