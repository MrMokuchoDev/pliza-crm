<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Lead\Queries\GetPaginatedLeadsQuery;
use App\Domain\CustomField\ValueObjects\SystemCustomFields;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Handler para obtener leads paginados con filtros.
 */
class GetPaginatedLeadsHandler
{
    public function handle(GetPaginatedLeadsQuery $query): LengthAwarePaginator
    {
        $builder = LeadModel::withCount(['deals', 'activeDeals'])
            ->with(['activeDeals' => fn ($q) => $q->with('salePhase')->limit(1)])
            ->with('assignedTo');

        // Filtro por usuario asignado (para vendedores)
        if ($query->onlyOwn && $query->userUuid) {
            $builder->where('assigned_to', $query->userUuid);
        }

        // Filtro por usuario específico (para managers filtrando por vendedor)
        if (! empty($query->filters['assigned_to'])) {
            $builder->where('assigned_to', $query->filters['assigned_to']);
        }

        if (! empty($query->filters['search'])) {
            $builder->searchInCustomFields(
                $query->filters['search'],
                SystemCustomFields::getLeadSearchableFields()
            );
        }

        if (! empty($query->filters['source'])) {
            $builder->where('source_type', $query->filters['source']);
        }

        return $builder->orderByDesc('created_at')->paginate($query->perPage);
    }
}
