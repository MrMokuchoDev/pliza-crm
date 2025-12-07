<?php

declare(strict_types=1);

namespace App\Application\Dashboard\Handlers;

use App\Application\Dashboard\Queries\GetLeadsBySourceQuery;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Handler para obtener leads agrupados por fuente.
 */
class GetLeadsBySourceHandler
{
    /**
     * @return array<string, int>
     */
    public function handle(GetLeadsBySourceQuery $query): array
    {
        $queryBuilder = LeadModel::query()
            ->select('source_type', DB::raw('COUNT(*) as count'))
            ->groupBy('source_type');

        if ($query->dateFrom) {
            $queryBuilder->where('created_at', '>=', Carbon::parse($query->dateFrom)->startOfDay());
        }

        if ($query->dateTo) {
            $queryBuilder->where('created_at', '<=', Carbon::parse($query->dateTo)->endOfDay());
        }

        $results = $queryBuilder->get();

        // Mapear a formato legible
        $sourceLabels = [
            'whatsapp_button' => 'WhatsApp',
            'phone_button' => 'Llamada',
            'contact_form' => 'Formulario',
            'manual' => 'Manual',
        ];

        $data = [];
        foreach ($results as $row) {
            // source_type puede ser un enum SourceType o null
            $sourceValue = $row->source_type?->value ?? $row->source_type ?? null;
            $label = $sourceLabels[$sourceValue] ?? $sourceValue ?? 'Desconocido';
            $data[$label] = $row->count;
        }

        return $data;
    }
}
