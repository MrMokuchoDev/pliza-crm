<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Lead\Queries\SearchLeadsQuery;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use Illuminate\Database\Eloquent\Collection;

/**
 * Handler para buscar leads por término.
 */
class SearchLeadsHandler
{
    /**
     * @return Collection<int, LeadModel>
     */
    public function handle(SearchLeadsQuery $query): Collection
    {
        $builder = LeadModel::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query->term}%")
                    ->orWhere('email', 'like', "%{$query->term}%")
                    ->orWhere('phone', 'like', "%{$query->term}%");
            });

        // Filtrar por usuario si es necesario
        if ($query->onlyOwn && $query->userUuid) {
            $builder->where('assigned_to', $query->userUuid);
        }

        return $builder
            ->withCount(['activeDeals'])
            ->orderByDesc('created_at')
            ->limit($query->limit)
            ->get();
    }
}
