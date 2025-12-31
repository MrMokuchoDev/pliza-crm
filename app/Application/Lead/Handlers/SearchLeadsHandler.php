<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Lead\Queries\SearchLeadsQuery;
use App\Domain\CustomField\ValueObjects\SystemCustomFields;
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
            ->searchInCustomFields($query->term, SystemCustomFields::getLeadSearchableFields());

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
