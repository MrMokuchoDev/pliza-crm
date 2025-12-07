<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Handlers;

use App\Application\SalePhase\Queries\GetOpenPhaseIdsQuery;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;

/**
 * Handler para obtener IDs de fases abiertas.
 */
class GetOpenPhaseIdsHandler
{
    /**
     * @return array<string>
     */
    public function handle(GetOpenPhaseIdsQuery $query): array
    {
        return SalePhaseModel::where('is_closed', false)
            ->pluck('id')
            ->toArray();
    }
}
