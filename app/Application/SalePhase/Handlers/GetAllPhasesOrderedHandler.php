<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Handlers;

use App\Application\SalePhase\Queries\GetAllPhasesOrderedQuery;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Illuminate\Support\Collection;

/**
 * Handler para obtener todas las fases ordenadas.
 */
class GetAllPhasesOrderedHandler
{
    /**
     * @return Collection<int, SalePhaseModel>
     */
    public function handle(GetAllPhasesOrderedQuery $query): Collection
    {
        return SalePhaseModel::orderBy('order')->get();
    }
}
