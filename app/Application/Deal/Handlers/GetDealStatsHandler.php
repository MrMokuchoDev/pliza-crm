<?php

declare(strict_types=1);

namespace App\Application\Deal\Handlers;

use App\Application\Deal\Queries\GetDealStatsQuery;
use App\Application\Deal\Services\DealValueCalculationService;
use App\Infrastructure\Persistence\Eloquent\DealModel;

/**
 * Handler para obtener estadísticas de deals.
 */
class GetDealStatsHandler
{
    public function __construct(
        private readonly DealValueCalculationService $dealValueCalculator
    ) {}

    /**
     * @return array{total: int, open: int, total_value: float}
     */
    public function handle(GetDealStatsQuery $query): array
    {
        $total = DealModel::count();

        if ($query->openPhaseIds === null || empty($query->openPhaseIds)) {
            return [
                'total' => $total,
                'open' => 0,
                'total_value' => 0,
            ];
        }

        $openDeals = DealModel::whereIn('sale_phase_id', $query->openPhaseIds)->count();

        // Calcular valor total usando servicio centralizado
        $totalValue = $this->dealValueCalculator->calculateTotalValueByPhaseIds($query->openPhaseIds);

        return [
            'total' => $total,
            'open' => $openDeals,
            'total_value' => $totalValue,
        ];
    }
}
