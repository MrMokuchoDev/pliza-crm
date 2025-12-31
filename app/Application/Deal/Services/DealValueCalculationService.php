<?php

declare(strict_types=1);

namespace App\Application\Deal\Services;

use App\Domain\CustomField\ValueObjects\SystemCustomFields;
use App\Infrastructure\Persistence\Eloquent\CustomFieldModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de aplicación para calcular valores monetarios de deals.
 *
 * CAPA: Application (orquesta queries de infrastructure)
 *
 * Centraliza la lógica de suma de valores que está duplicada en múltiples handlers.
 * Este servicio accede directamente a modelos Eloquent y DB, por lo tanto pertenece
 * a la capa Application, NO a Domain.
 */
class DealValueCalculationService
{
    /**
     * Obtener el Custom Field que representa el valor monetario del deal.
     * Usa cache para evitar queries repetidas.
     */
    protected function getValueField(): ?CustomFieldModel
    {
        return Cache::remember('custom_field_deal_value', 3600, function () {
            return CustomFieldModel::where('entity_type', 'deal')
                ->where('name', SystemCustomFields::DEAL_VALUE)
                ->first();
        });
    }

    /**
     * Calcular el valor total de deals por sus IDs.
     *
     * @param array<string> $dealIds Array de UUIDs de deals
     * @return float Suma total de valores
     */
    public function calculateTotalValueByDealIds(array $dealIds): float
    {
        if (empty($dealIds)) {
            return 0.0;
        }

        $valueField = $this->getValueField();
        if (!$valueField) {
            return 0.0;
        }

        return (float) DB::table('custom_field_values')
            ->whereIn('entity_id', $dealIds)
            ->where('custom_field_id', $valueField->id)
            ->where('entity_type', 'deal')
            ->sum(DB::raw('CAST(value AS DECIMAL(12,2))')) ?? 0.0;
    }

    /**
     * Calcular el valor total de deals por fase(s) de venta.
     *
     * @param array<string> $phaseIds Array de UUIDs de fases de venta
     * @return float Suma total de valores
     */
    public function calculateTotalValueByPhaseIds(array $phaseIds): float
    {
        if (empty($phaseIds)) {
            return 0.0;
        }

        $valueField = $this->getValueField();
        if (!$valueField) {
            return 0.0;
        }

        return (float) DB::table('custom_field_values')
            ->whereIn('entity_id', function ($subQuery) use ($phaseIds) {
                $subQuery->select('id')
                    ->from('deals')
                    ->whereIn('sale_phase_id', $phaseIds)
                    ->whereNull('deleted_at');
            })
            ->where('custom_field_id', $valueField->id)
            ->where('entity_type', 'deal')
            ->sum(DB::raw('CAST(value AS DECIMAL(12,2))')) ?? 0.0;
    }

    /**
     * Calcular el valor total de deals ganados (fases cerradas y ganadas).
     *
     * @return float Suma total de valores de deals ganados
     */
    public function calculateTotalWonValue(): float
    {
        $valueField = $this->getValueField();
        if (!$valueField) {
            return 0.0;
        }

        return (float) DB::table('custom_field_values')
            ->whereIn('entity_id', function ($subQuery) {
                $subQuery->select('deals.id')
                    ->from('deals')
                    ->join('sale_phases', 'deals.sale_phase_id', '=', 'sale_phases.id')
                    ->where('sale_phases.is_closed', true)
                    ->where('sale_phases.is_won', true)
                    ->whereNull('deals.deleted_at');
            })
            ->where('custom_field_id', $valueField->id)
            ->where('entity_type', 'deal')
            ->sum(DB::raw('CAST(value AS DECIMAL(12,2))')) ?? 0.0;
    }

    /**
     * Calcular el valor total de deals filtrados por sitio y periodo.
     *
     * @param string|null $siteId UUID del sitio (null = todos los sitios)
     * @param string|null $dateFrom Fecha desde (Y-m-d)
     * @param string|null $dateTo Fecha hasta (Y-m-d)
     * @return float Suma total de valores
     */
    public function calculateTotalValueBySiteAndPeriod(
        ?string $siteId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): float {
        $valueField = $this->getValueField();
        if (!$valueField) {
            return 0.0;
        }

        return (float) DB::table('custom_field_values')
            ->whereIn('entity_id', function ($subQuery) use ($siteId, $dateFrom, $dateTo) {
                $query = $subQuery->select('deals.id')
                    ->from('deals')
                    ->join('leads', 'deals.lead_id', '=', 'leads.id')
                    ->join('sale_phases', 'deals.sale_phase_id', '=', 'sale_phases.id')
                    ->where('sale_phases.is_closed', true)
                    ->where('sale_phases.is_won', true)
                    ->whereNull('deals.deleted_at');

                if ($siteId) {
                    $query->where('leads.source_site_id', $siteId);
                }

                if ($dateFrom) {
                    $query->where('deals.created_at', '>=', $dateFrom . ' 00:00:00');
                }

                if ($dateTo) {
                    $query->where('deals.created_at', '<=', $dateTo . ' 23:59:59');
                }

                return $query;
            })
            ->where('custom_field_id', $valueField->id)
            ->where('entity_type', 'deal')
            ->sum(DB::raw('CAST(value AS DECIMAL(12,2))')) ?? 0.0;
    }

    /**
     * Calcular valores totales agrupados por fase de venta.
     *
     * @param string|null $dateFrom Fecha desde (Y-m-d)
     * @param string|null $dateTo Fecha hasta (Y-m-d)
     * @return array<string, float> Array asociativo [phaseId => totalValue]
     */
    public function calculateValuesByPhase(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $valueField = $this->getValueField();
        if (!$valueField) {
            return [];
        }

        $query = DB::table('deals')
            ->join('custom_field_values', function ($join) use ($valueField) {
                $join->on('deals.id', '=', 'custom_field_values.entity_id')
                    ->where('custom_field_values.entity_type', '=', 'deal')
                    ->where('custom_field_values.custom_field_id', '=', $valueField->id);
            })
            ->select('deals.sale_phase_id', DB::raw('COALESCE(SUM(CAST(custom_field_values.value AS DECIMAL(12,2))), 0) as total_value'))
            ->whereNull('deals.deleted_at')
            ->groupBy('deals.sale_phase_id');

        if ($dateFrom) {
            $query->where('deals.created_at', '>=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo) {
            $query->where('deals.created_at', '<=', $dateTo . ' 23:59:59');
        }

        $results = $query->get();

        $valuesByPhase = [];
        foreach ($results as $result) {
            $valuesByPhase[$result->sale_phase_id] = (float) $result->total_value;
        }

        return $valuesByPhase;
    }
}
