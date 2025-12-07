<?php

declare(strict_types=1);

namespace App\Application\Dashboard\Handlers;

use App\Application\Dashboard\Queries\GetDealsByPhaseQuery;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Handler para obtener deals agrupados por fase.
 */
class GetDealsByPhaseHandler
{
    /**
     * @return array<int, array{name: string, count: int, value: float, color: string}>
     */
    public function handle(GetDealsByPhaseQuery $query): array
    {
        // Obtener todas las fases ordenadas
        $phases = SalePhaseModel::orderBy('order')->get()->keyBy('id');

        $queryBuilder = DealModel::query()
            ->select('sale_phase_id', DB::raw('COUNT(*) as count'), DB::raw('COALESCE(SUM(value), 0) as total_value'))
            ->groupBy('sale_phase_id');

        if ($query->dateFrom) {
            $queryBuilder->where('created_at', '>=', Carbon::parse($query->dateFrom)->startOfDay());
        }

        if ($query->dateTo) {
            $queryBuilder->where('created_at', '<=', Carbon::parse($query->dateTo)->endOfDay());
        }

        $results = $queryBuilder->get()->keyBy('sale_phase_id');

        // Construir respuesta con todas las fases
        $data = [];
        foreach ($phases as $phaseId => $phase) {
            $result = $results->get($phaseId);
            $data[] = [
                'id' => $phase->id,
                'name' => $phase->name,
                'count' => $result ? $result->count : 0,
                'value' => $result ? (float) $result->total_value : 0,
                'color' => $phase->color,
                'is_closed' => $phase->is_closed,
                'is_won' => $phase->is_won,
            ];
        }

        return $data;
    }
}
