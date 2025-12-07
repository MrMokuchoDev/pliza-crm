<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Handlers;

use App\Application\SalePhase\Queries\FindSalePhaseQuery;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;

/**
 * Handler para buscar una Fase de Venta por ID.
 */
class FindSalePhaseHandler
{
    public function handle(FindSalePhaseQuery $query): ?SalePhaseModel
    {
        return SalePhaseModel::find($query->phaseId);
    }
}
