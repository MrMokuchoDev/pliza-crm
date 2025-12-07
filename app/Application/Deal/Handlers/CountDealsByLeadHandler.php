<?php

declare(strict_types=1);

namespace App\Application\Deal\Handlers;

use App\Application\Deal\Queries\CountDealsByLeadQuery;
use App\Infrastructure\Persistence\Eloquent\DealModel;

/**
 * Handler para contar deals de un Lead.
 */
class CountDealsByLeadHandler
{
    public function handle(CountDealsByLeadQuery $query): int
    {
        return DealModel::where('lead_id', $query->leadId)->count();
    }
}
