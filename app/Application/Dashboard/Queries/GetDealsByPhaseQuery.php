<?php

declare(strict_types=1);

namespace App\Application\Dashboard\Queries;

/**
 * Query para obtener deals agrupados por fase.
 */
readonly class GetDealsByPhaseQuery
{
    public function __construct(
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {}
}
