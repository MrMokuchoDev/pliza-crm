<?php

declare(strict_types=1);

namespace App\Application\Deal\Queries;

/**
 * Query para obtener estadísticas de deals.
 */
readonly class GetDealStatsQuery
{
    /**
     * @param  array<string>|null  $openPhaseIds
     */
    public function __construct(
        public ?array $openPhaseIds = null,
    ) {}
}
