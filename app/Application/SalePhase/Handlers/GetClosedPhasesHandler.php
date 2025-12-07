<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Handlers;

use App\Application\SalePhase\Queries\GetClosedPhasesQuery;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Illuminate\Support\Collection;

/**
 * Handler para obtener fases cerradas.
 */
class GetClosedPhasesHandler
{
    /**
     * @return Collection<int, SalePhaseModel>
     */
    public function handle(GetClosedPhasesQuery $query): Collection
    {
        return SalePhaseModel::where('is_closed', true)
            ->orderBy('order')
            ->get();
    }
}
