<?php

declare(strict_types=1);

namespace App\Application\Site\Queries;

final class GetAllSitesQuery
{
    public function __construct(
        public readonly string $orderBy = 'created_at',
        public readonly string $orderDirection = 'desc',
    ) {}
}
