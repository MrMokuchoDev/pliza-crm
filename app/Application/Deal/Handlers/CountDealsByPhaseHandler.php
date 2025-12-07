<?php

declare(strict_types=1);

namespace App\Application\Deal\Handlers;

use App\Application\Deal\Queries\CountDealsByPhaseQuery;
use App\Infrastructure\Persistence\Eloquent\DealModel;

/**
 * Handler para contar deals en una fase.
 */
class CountDealsByPhaseHandler
{
    public function handle(CountDealsByPhaseQuery $query): int
    {
        return DealModel::where('sale_phase_id', $query->phaseId)->count();
    }
}
