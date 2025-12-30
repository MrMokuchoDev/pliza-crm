<?php

declare(strict_types=1);

namespace App\Application\Dashboard\Handlers;

use App\Application\Dashboard\Queries\GetDealsByPhaseQuery;
use App\Application\Deal\Services\DealValueCalculationService;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Handler para obtener deals agrupados por fase.
 */
class GetDealsByPhaseHandler
{
    public function __construct(
        private readonly DealValueCalculationService $dealValueCalculator
    ) {}

    /**
     * @return array<int, array{name: string, count: int, value: float, color: string}>
     */
    public function handle(GetDealsByPhaseQuery $query): array
    {
        // Obtener todas las fases ordenadas
        $phases = SalePhaseModel::orderBy('order')->get()->keyBy('id');

        // Contar deals por fase
        $queryBuilder = DealModel::query()
            ->select('sale_phase_id', DB::raw('COUNT(*) as count'))
            ->groupBy('sale_phase_id');

        if ($query->dateFrom) {
            $queryBuilder->where('created_at', '>=', Carbon::parse($query->dateFrom)->startOfDay());
        }

        if ($query->dateTo) {
            $queryBuilder->where('created_at', '<=', Carbon::parse($query->dateTo)->endOfDay());
        }

        $results = $queryBuilder->get()->keyBy('sale_phase_id');

        // Calcular valores por fase usando servicio centralizado
        $valuesByPhase = $this->dealValueCalculator->calculateValuesByPhase(
            $query->dateFrom,
            $query->dateTo
        );

        // Construir respuesta con todas las fases
        $data = [];
        foreach ($phases as $phaseId => $phase) {
            $result = $results->get($phaseId);

            $data[] = [
                'id' => $phase->id,
                'name' => $phase->name,
                'count' => $result ? $result->count : 0,
                'value' => $valuesByPhase[$phaseId] ?? 0.0,
                'color' => $phase->color,
                'is_closed' => $phase->is_closed,
                'is_won' => $phase->is_won,
            ];
        }

        return $data;
    }
}
