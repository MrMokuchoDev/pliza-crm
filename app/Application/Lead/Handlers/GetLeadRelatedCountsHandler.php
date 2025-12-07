<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Lead\Queries\GetLeadRelatedCountsQuery;
use App\Infrastructure\Persistence\Eloquent\LeadModel;

/**
 * Handler para obtener conteos de relaciones de un Lead.
 */
class GetLeadRelatedCountsHandler
{
    /**
     * @return array{deals: int, notes: int}
     */
    public function handle(GetLeadRelatedCountsQuery $query): array
    {
        $lead = LeadModel::withCount(['deals', 'notes'])->find($query->leadId);

        if (! $lead) {
            return ['deals' => 0, 'notes' => 0];
        }

        return [
            'deals' => $lead->deals_count,
            'notes' => $lead->notes_count,
        ];
    }
}
