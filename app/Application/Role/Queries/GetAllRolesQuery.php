<?php

declare(strict_types=1);

namespace App\Application\Role\Queries;

final readonly class GetAllRolesQuery
{
    public function __construct(
        public string $orderBy = 'level',
        public string $orderDirection = 'desc',
    ) {}
}
