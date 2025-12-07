<?php

declare(strict_types=1);

namespace App\Application\Dashboard\Handlers;

use App\Application\Dashboard\DTOs\DashboardStatsData;
use App\Application\Dashboard\Queries\GetDashboardStatsQuery;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Carbon\Carbon;

/**
 * Handler para obtener estadísticas generales del dashboard.
 */
class GetDashboardStatsHandler
{
    /**
     * @return DashboardStatsData
     */
    public function handle(GetDashboardStatsQuery $query): DashboardStatsData
    {
        $dateFrom = $query->dateFrom ? Carbon::parse($query->dateFrom)->startOfDay() : null;
        $dateTo = $query->dateTo ? Carbon::parse($query->dateTo)->endOfDay() : null;

        // Obtener IDs de fases cerradas (ganadas y perdidas)
        $wonPhaseIds = SalePhaseModel::where('is_closed', true)
            ->where('is_won', true)
            ->pluck('id')
            ->toArray();

        $lostPhaseIds = SalePhaseModel::where('is_closed', true)
            ->where('is_won', false)
            ->pluck('id')
            ->toArray();

        $closedPhaseIds = array_merge($wonPhaseIds, $lostPhaseIds);

        // Total de leads
        $totalLeads = LeadModel::count();

        // Total de deals
        $dealsQuery = DealModel::query();
        $totalDeals = $dealsQuery->count();

        // Deals abiertos (no en fases cerradas)
        $openDeals = DealModel::whereNotIn('sale_phase_id', $closedPhaseIds)->count();

        // Deals ganados
        $wonDeals = DealModel::whereIn('sale_phase_id', $wonPhaseIds)->count();

        // Deals perdidos
        $lostDeals = DealModel::whereIn('sale_phase_id', $lostPhaseIds)->count();

        // Valor total de deals ganados
        $totalWonValue = DealModel::whereIn('sale_phase_id', $wonPhaseIds)
            ->sum('value') ?? 0;

        // Tasa de conversión (ganados / cerrados totales * 100)
        $closedDeals = $wonDeals + $lostDeals;
        $conversionRate = $closedDeals > 0 ? round(($wonDeals / $closedDeals) * 100, 1) : 0;

        // Leads este mes
        $leadsThisMonth = LeadModel::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Deals este mes
        $dealsThisMonth = DealModel::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        return new DashboardStatsData(
            totalLeads: $totalLeads,
            totalDeals: $totalDeals,
            openDeals: $openDeals,
            wonDeals: $wonDeals,
            lostDeals: $lostDeals,
            totalWonValue: (float) $totalWonValue,
            conversionRate: (float) $conversionRate,
            leadsThisMonth: $leadsThisMonth,
            dealsThisMonth: $dealsThisMonth,
        );
    }
}
