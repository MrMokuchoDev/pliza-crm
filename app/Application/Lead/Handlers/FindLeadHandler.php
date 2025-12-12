<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Lead\Queries\FindLeadQuery;
use App\Infrastructure\Persistence\Eloquent\LeadModel;

/**
 * Handler para buscar un Lead por ID.
 */
class FindLeadHandler
{
    public function handle(FindLeadQuery $query): ?LeadModel
    {
        $builder = LeadModel::query();

        if ($query->withRelations) {
            $builder->with(['notes', 'sourceSite', 'deals.salePhase', 'assignedTo']);
        }

        return $builder->find($query->leadId);
    }
}
