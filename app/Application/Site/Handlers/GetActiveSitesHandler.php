<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Queries\GetActiveSitesQuery;
use App\Infrastructure\Persistence\Eloquent\SiteModel;
use Illuminate\Support\Collection;

final class GetActiveSitesHandler
{
    /**
     * @return Collection<int, SiteModel>
     */
    public function handle(GetActiveSitesQuery $query): Collection
    {
        return SiteModel::where('is_active', true)
            ->orderBy($query->orderBy, $query->orderDirection)
            ->get();
    }
}
