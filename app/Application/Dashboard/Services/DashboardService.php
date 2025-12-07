<?php

declare(strict_types=1);

namespace App\Application\Dashboard\Services;

use App\Application\Dashboard\DTOs\DashboardStatsData;
use App\Application\Dashboard\Handlers\GetConversionFunnelHandler;
use App\Application\Dashboard\Handlers\GetDashboardStatsHandler;
use App\Application\Dashboard\Handlers\GetDealsByPhaseHandler;
use App\Application\Dashboard\Handlers\GetLeadsBySourceHandler;
use App\Application\Dashboard\Handlers\GetLeadsTrendHandler;
use App\Application\Dashboard\Queries\GetConversionFunnelQuery;
use App\Application\Dashboard\Queries\GetDashboardStatsQuery;
use App\Application\Dashboard\Queries\GetDealsByPhaseQuery;
use App\Application\Dashboard\Queries\GetLeadsBySourceQuery;
use App\Application\Dashboard\Queries\GetLeadsTrendQuery;

/**
 * Servicio de aplicación para el Dashboard.
 * Orquesta las queries y handlers.
 */
class DashboardService
{
    public function __construct(
        private readonly GetDashboardStatsHandler $statsHandler,
        private readonly GetLeadsBySourceHandler $leadsBySourceHandler,
        private readonly GetDealsByPhaseHandler $dealsByPhaseHandler,
        private readonly GetConversionFunnelHandler $conversionFunnelHandler,
        private readonly GetLeadsTrendHandler $leadsTrendHandler,
    ) {}

    /**
     * Obtener estadísticas generales del dashboard.
     */
    public function getStats(?string $dateFrom = null, ?string $dateTo = null): DashboardStatsData
    {
        $query = new GetDashboardStatsQuery($dateFrom, $dateTo);

        return $this->statsHandler->handle($query);
    }

    /**
     * Obtener leads agrupados por fuente.
     *
     * @return array<string, int>
     */
    public function getLeadsBySource(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = new GetLeadsBySourceQuery($dateFrom, $dateTo);

        return $this->leadsBySourceHandler->handle($query);
    }

    /**
     * Obtener deals agrupados por fase.
     *
     * @return array<int, array{name: string, count: int, value: float, color: string}>
     */
    public function getDealsByPhase(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = new GetDealsByPhaseQuery($dateFrom, $dateTo);

        return $this->dealsByPhaseHandler->handle($query);
    }

    /**
     * Obtener funnel de conversión.
     *
     * @return array<int, array{name: string, count: int, percentage: float, color: string}>
     */
    public function getConversionFunnel(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = new GetConversionFunnelQuery($dateFrom, $dateTo);

        return $this->conversionFunnelHandler->handle($query);
    }

    /**
     * Obtener tendencia de leads.
     *
     * @return array<int, array{date: string, count: int}>
     */
    public function getLeadsTrend(string $period = 'daily', int $limit = 30): array
    {
        $query = new GetLeadsTrendQuery($period, $limit);

        return $this->leadsTrendHandler->handle($query);
    }

    /**
     * Obtener todos los datos del dashboard de una vez.
     */
    public function getAllDashboardData(?string $dateFrom = null, ?string $dateTo = null): array
    {
        return [
            'stats' => $this->getStats($dateFrom, $dateTo)->toArray(),
            'leads_by_source' => $this->getLeadsBySource($dateFrom, $dateTo),
            'deals_by_phase' => $this->getDealsByPhase($dateFrom, $dateTo),
            'conversion_funnel' => $this->getConversionFunnel($dateFrom, $dateTo),
            'leads_trend' => $this->getLeadsTrend('daily', 30),
        ];
    }
}
