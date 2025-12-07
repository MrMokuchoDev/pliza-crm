<?php

declare(strict_types=1);

namespace App\Application\Dashboard\Queries;

/**
 * Query para obtener estadísticas generales del dashboard.
 */
readonly class GetDashboardStatsQuery
{
    public function __construct(
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {}
}
