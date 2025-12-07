<?php

declare(strict_types=1);

namespace App\Application\Dashboard\Handlers;

use App\Application\Dashboard\Queries\GetLeadsTrendQuery;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Handler para obtener tendencia de leads por período.
 */
class GetLeadsTrendHandler
{
    /**
     * @return array<int, array{date: string, count: int}>
     */
    public function handle(GetLeadsTrendQuery $query): array
    {
        $format = match ($query->period) {
            'weekly' => '%Y-%u',   // Año-Semana
            'monthly' => '%Y-%m', // Año-Mes
            default => '%Y-%m-%d', // Año-Mes-Día
        };

        $dateStart = match ($query->period) {
            'weekly' => Carbon::now()->subWeeks($query->limit),
            'monthly' => Carbon::now()->subMonths($query->limit),
            default => Carbon::now()->subDays($query->limit),
        };

        $results = LeadModel::query()
            ->select(DB::raw("DATE_FORMAT(created_at, '{$format}') as period"), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $dateStart)
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Generar todas las fechas del período para llenar gaps
        $data = [];
        $current = $dateStart->copy();
        $end = Carbon::now();

        while ($current <= $end) {
            $periodKey = match ($query->period) {
                'weekly' => $current->format('Y-W'),
                'monthly' => $current->format('Y-m'),
                default => $current->format('Y-m-d'),
            };

            $displayDate = match ($query->period) {
                'weekly' => 'Sem '.$current->weekOfYear,
                'monthly' => $current->translatedFormat('M Y'),
                default => $current->format('d M'),
            };

            $data[$periodKey] = [
                'date' => $displayDate,
                'count' => 0,
            ];

            $current = match ($query->period) {
                'weekly' => $current->addWeek(),
                'monthly' => $current->addMonth(),
                default => $current->addDay(),
            };
        }

        // Llenar con datos reales
        foreach ($results as $row) {
            $key = match ($query->period) {
                'weekly' => substr($row->period, 0, 4).'-'.str_pad(substr($row->period, 5), 2, '0', STR_PAD_LEFT),
                default => $row->period,
            };
            if (isset($data[$key])) {
                $data[$key]['count'] = $row->count;
            }
        }

        return array_values($data);
    }
}
