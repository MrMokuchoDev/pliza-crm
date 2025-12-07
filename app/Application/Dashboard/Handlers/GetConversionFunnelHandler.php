<?php

declare(strict_types=1);

namespace App\Application\Dashboard\Handlers;

use App\Application\Dashboard\Queries\GetConversionFunnelQuery;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Carbon\Carbon;

/**
 * Handler para obtener el funnel de conversión.
 */
class GetConversionFunnelHandler
{
    /**
     * @return array<int, array{name: string, count: int, percentage: float, color: string}>
     */
    public function handle(GetConversionFunnelQuery $query): array
    {
        // Obtener fases abiertas ordenadas (el funnel solo muestra fases activas)
        $phases = SalePhaseModel::where('is_closed', false)
            ->orderBy('order')
            ->get();

        // Obtener fase ganada para el final del funnel
        $wonPhase = SalePhaseModel::where('is_closed', true)
            ->where('is_won', true)
            ->first();

        $queryBuilder = DealModel::query();

        if ($query->dateFrom) {
            $queryBuilder->where('created_at', '>=', Carbon::parse($query->dateFrom)->startOfDay());
        }

        if ($query->dateTo) {
            $queryBuilder->where('created_at', '<=', Carbon::parse($query->dateTo)->endOfDay());
        }

        // Total de deals para calcular porcentajes
        $totalDeals = (clone $queryBuilder)->count();

        if ($totalDeals === 0) {
            return [];
        }

        $funnel = [];

        // Agregar fases abiertas al funnel
        foreach ($phases as $phase) {
            $count = (clone $queryBuilder)
                ->where('sale_phase_id', $phase->id)
                ->count();

            $funnel[] = [
                'name' => $phase->name,
                'count' => $count,
                'percentage' => round(($count / $totalDeals) * 100, 1),
                'color' => $phase->color,
            ];
        }

        // Agregar fase ganada al final
        if ($wonPhase) {
            $wonCount = (clone $queryBuilder)
                ->where('sale_phase_id', $wonPhase->id)
                ->count();

            $funnel[] = [
                'name' => $wonPhase->name,
                'count' => $wonCount,
                'percentage' => round(($wonCount / $totalDeals) * 100, 1),
                'color' => $wonPhase->color,
            ];
        }

        return $funnel;
    }
}
