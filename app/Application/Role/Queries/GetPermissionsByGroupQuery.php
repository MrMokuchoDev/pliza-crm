<?php

declare(strict_types=1);

namespace App\Application\Role\Queries;

final readonly class GetPermissionsByGroupQuery
{
    public function __construct(
        public string $group,
    ) {}
}
