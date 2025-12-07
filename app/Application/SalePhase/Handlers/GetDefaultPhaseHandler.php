<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Handlers;

use App\Application\SalePhase\Queries\GetDefaultPhaseQuery;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;

/**
 * Handler para obtener la fase por defecto.
 */
class GetDefaultPhaseHandler
{
    public function handle(GetDefaultPhaseQuery $query): ?SalePhaseModel
    {
        $defaultPhase = SalePhaseModel::where('is_default', true)->first();

        if ($defaultPhase || ! $query->fallbackToFirstOpen) {
            return $defaultPhase;
        }

        return SalePhaseModel::where('is_closed', false)
            ->orderBy('order')
            ->first();
    }
}
