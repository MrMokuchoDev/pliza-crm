<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Queries\GetSiteStatisticsQuery;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use App\Infrastructure\Persistence\Eloquent\SiteModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class GetSiteStatisticsHandler
{
    /**
     * @return array{
     *     site: SiteModel|null,
     *     totals: array{leads: int, deals: int, won: int, lost: int, value: float},
     *     conversion_rate: float,
     *     leads_by_source: array,
     *     leads_by_period: array,
     *     deals_by_phase: array,
     *     recent_leads: \Illuminate\Database\Eloquent\Collection
     * }
     */
    public function handle(GetSiteStatisticsQuery $query): array
    {
        $site = SiteModel::find($query->siteId);

        if (! $site) {
            return [
                'site' => null,
                'totals' => ['leads' => 0, 'deals' => 0, 'won' => 0, 'lost' => 0, 'value' => 0],
                'conversion_rate' => 0,
                'leads_by_source' => [],
                'leads_by_period' => [],
                'deals_by_phase' => [],
                'recent_leads' => collect(),
            ];
        }

        $dateFrom = $query->dateFrom ? Carbon::parse($query->dateFrom)->startOfDay() : null;
        $dateTo = $query->dateTo ? Carbon::parse($query->dateTo)->endOfDay() : null;

        // Query base de leads
        $leadsQuery = LeadModel::where('source_site_id', $query->siteId);

        if ($dateFrom) {
            $leadsQuery->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $leadsQuery->where('created_at', '<=', $dateTo);
        }

        // Totales
        $totalLeads = (clone $leadsQuery)->count();
        $leadIds = (clone $leadsQuery)->pluck('id')->toArray();

        // Deals relacionados a esos leads
        $dealsQuery = DealModel::whereIn('lead_id', $leadIds);
        $totalDeals = (clone $dealsQuery)->count();

        // Deals ganados y perdidos
        $wonDeals = (clone $dealsQuery)
            ->whereHas('salePhase', fn ($q) => $q->where('is_closed', true)->where('is_won', true))
            ->count();
        $lostDeals = (clone $dealsQuery)
            ->whereHas('salePhase', fn ($q) => $q->where('is_closed', true)->where('is_won', false))
            ->count();

        // Valor total de deals ganados
        $totalValue = (clone $dealsQuery)
            ->whereHas('salePhase', fn ($q) => $q->where('is_closed', true)->where('is_won', true))
            ->sum('value');

        // Tasa de conversión real:
        // Un lead se considera "convertido" si tiene al menos un negocio que:
        // 1. Esté en una fase ganada (is_won = true), O
        // 2. Haya avanzado más allá de la primera fase (order > 1) y no esté perdido
        $firstPhaseOrder = SalePhaseModel::where('is_closed', false)->min('order') ?? 1;

        $convertedLeadsCount = LeadModel::whereIn('id', $leadIds)
            ->whereHas('deals', function ($q) use ($firstPhaseOrder) {
                $q->whereHas('salePhase', function ($phaseQuery) use ($firstPhaseOrder) {
                    $phaseQuery->where(function ($sub) use ($firstPhaseOrder) {
                        // Ganado
                        $sub->where(function ($won) {
                            $won->where('is_closed', true)->where('is_won', true);
                        })
                        // O avanzó más allá de la primera fase (sin estar perdido)
                        ->orWhere(function ($advanced) use ($firstPhaseOrder) {
                            $advanced->where('order', '>', $firstPhaseOrder)
                                ->where(function ($notLost) {
                                    $notLost->where('is_closed', false)
                                        ->orWhere(function ($closedWon) {
                                            $closedWon->where('is_closed', true)->where('is_won', true);
                                        });
                                });
                        });
                    });
                });
            })
            ->count();

        $conversionRate = $totalLeads > 0 ? round(($convertedLeadsCount / $totalLeads) * 100, 1) : 0;

        // Leads por tipo de fuente (source_type)
        $leadsBySource = (clone $leadsQuery)
            ->select('source_type', DB::raw('COUNT(*) as count'))
            ->groupBy('source_type')
            ->pluck('count', 'source_type')
            ->toArray();

        // Leads por período (últimos 30 días por defecto, o según filtro)
        $leadsByPeriod = $this->getLeadsByPeriod($query->siteId, $dateFrom, $dateTo);

        // Deals por fase
        $dealsByPhase = DealModel::whereIn('lead_id', $leadIds)
            ->join('sale_phases', 'deals.sale_phase_id', '=', 'sale_phases.id')
            ->select('sale_phases.name', 'sale_phases.color', DB::raw('COUNT(*) as count'))
            ->groupBy('sale_phases.id', 'sale_phases.name', 'sale_phases.color')
            ->orderBy('sale_phases.order')
            ->get()
            ->toArray();

        // Leads recientes
        $recentLeads = (clone $leadsQuery)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return [
            'site' => $site,
            'totals' => [
                'leads' => $totalLeads,
                'deals' => $totalDeals,
                'won' => $wonDeals,
                'lost' => $lostDeals,
                'value' => (float) $totalValue,
            ],
            'conversion_rate' => $conversionRate,
            'leads_by_source' => $leadsBySource,
            'leads_by_period' => $leadsByPeriod,
            'deals_by_phase' => $dealsByPhase,
            'recent_leads' => $recentLeads,
        ];
    }

    private function getLeadsByPeriod(string $siteId, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        // Si no hay fechas, usar últimos 30 días
        if (! $dateFrom) {
            $dateFrom = Carbon::now()->subDays(30)->startOfDay();
        }
        if (! $dateTo) {
            $dateTo = Carbon::now()->endOfDay();
        }

        $diffInDays = $dateFrom->diffInDays($dateTo);

        // Determinar agrupación según rango
        if ($diffInDays <= 31) {
            // Agrupar por día
            $format = '%Y-%m-%d';
            $labelFormat = 'd/m';
        } elseif ($diffInDays <= 180) {
            // Agrupar por semana
            $format = '%Y-%U';
            $labelFormat = 'W';
        } else {
            // Agrupar por mes
            $format = '%Y-%m';
            $labelFormat = 'm/Y';
        }

        $results = LeadModel::where('source_site_id', $siteId)
            ->where('created_at', '>=', $dateFrom)
            ->where('created_at', '<=', $dateTo)
            ->select(DB::raw("DATE_FORMAT(created_at, '{$format}') as period"), DB::raw('COUNT(*) as count'))
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('count', 'period')
            ->toArray();

        // Generar todas las fechas del rango para tener datos completos
        $data = [];
        $current = $dateFrom->copy();

        while ($current <= $dateTo) {
            if ($diffInDays <= 31) {
                $key = $current->format('Y-m-d');
                $label = $current->format('d/m');
                $current->addDay();
            } elseif ($diffInDays <= 180) {
                $key = $current->format('Y-W');
                $label = 'Sem ' . $current->format('W');
                $current->addWeek();
            } else {
                $key = $current->format('Y-m');
                $label = $current->format('M Y');
                $current->addMonth();
            }

            $data[] = [
                'label' => $label,
                'count' => $results[$key] ?? 0,
            ];
        }

        return $data;
    }
}
