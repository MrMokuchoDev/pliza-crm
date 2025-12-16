<?php

declare(strict_types=1);

namespace App\Application\Role\Queries;

final readonly class GetRolePermissionsQuery
{
    public function __construct(
        public string $roleId,
    ) {}
}
