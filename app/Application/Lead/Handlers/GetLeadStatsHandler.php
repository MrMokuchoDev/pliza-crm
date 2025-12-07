<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Lead\Queries\GetLeadStatsQuery;
use App\Infrastructure\Persistence\Eloquent\LeadModel;

/**
 * Handler para obtener estadísticas de leads.
 */
class GetLeadStatsHandler
{
    /**
     * @return array{total: int, with_deals: int, without_deals: int}
     */
    public function handle(GetLeadStatsQuery $query): array
    {
        $total = LeadModel::count();
        $withDeals = LeadModel::has('deals')->count();

        return [
            'total' => $total,
            'with_deals' => $withDeals,
            'without_deals' => $total - $withDeals,
        ];
    }
}
