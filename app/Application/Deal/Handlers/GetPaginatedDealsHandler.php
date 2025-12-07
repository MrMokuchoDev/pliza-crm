<?php

declare(strict_types=1);

namespace App\Application\Deal\Handlers;

use App\Application\Deal\Queries\GetPaginatedDealsQuery;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Handler para obtener deals paginados con filtros.
 */
class GetPaginatedDealsHandler
{
    public function handle(GetPaginatedDealsQuery $query): LengthAwarePaginator
    {
        $builder = DealModel::with(['lead', 'salePhase']);

        if (! empty($query->filters['search'])) {
            $search = $query->filters['search'];
            $builder->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('lead', function ($lq) use ($search) {
                        $lq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($query->filters['phase_id'])) {
            $builder->where('sale_phase_id', $query->filters['phase_id']);
        }

        if (! empty($query->filters['source_type'])) {
            $builder->whereHas('lead', fn ($lq) => $lq->where('source_type', $query->filters['source_type']));
        }

        return $builder->orderByDesc('created_at')->paginate($query->perPage);
    }
}
