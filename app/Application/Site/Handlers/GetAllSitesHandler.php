<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Queries\GetAllSitesQuery;
use App\Infrastructure\Persistence\Eloquent\SiteModel;
use Illuminate\Support\Collection;

final class GetAllSitesHandler
{
    /**
     * @return Collection<int, SiteModel>
     */
    public function handle(GetAllSitesQuery $query): Collection
    {
        return SiteModel::orderBy($query->orderBy, $query->orderDirection)->get();
    }
}
