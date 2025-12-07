<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Lead\Queries\GetPaginatedLeadsQuery;
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
            ->with(['activeDeals' => fn ($q) => $q->with('salePhase')->limit(1)]);

        if (! empty($query->filters['search'])) {
            $search = $query->filters['search'];
            $builder->where(function ($sq) use ($search) {
                $sq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (! empty($query->filters['source'])) {
            $builder->where('source_type', $query->filters['source']);
        }

        return $builder->orderByDesc('created_at')->paginate($query->perPage);
    }
}
