<?php

declare(strict_types=1);

namespace App\Application\Dashboard\Queries;

/**
 * Query para obtener tendencia de leads por período.
 */
readonly class GetLeadsTrendQuery
{
    public function __construct(
        public string $period = 'daily', // daily, weekly, monthly
        public int $limit = 30,
    ) {}
}
