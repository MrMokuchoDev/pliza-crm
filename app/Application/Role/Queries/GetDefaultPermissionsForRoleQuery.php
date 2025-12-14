<?php

declare(strict_types=1);

namespace App\Application\Role\Queries;

final readonly class GetDefaultPermissionsForRoleQuery
{
    public function __construct(
        public string $roleName,
    ) {}
}
