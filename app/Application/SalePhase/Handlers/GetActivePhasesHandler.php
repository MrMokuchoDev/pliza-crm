<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Handlers;

use App\Application\SalePhase\Queries\GetActivePhasesQuery;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Illuminate\Support\Collection;

/**
 * Handler para obtener fases activas (no cerradas).
 */
class GetActivePhasesHandler
{
    /**
     * @return Collection<int, SalePhaseModel>
     */
    public function handle(GetActivePhasesQuery $query): Collection
    {
        return SalePhaseModel::where('is_closed', false)
            ->orderBy('order')
            ->get();
    }
}
