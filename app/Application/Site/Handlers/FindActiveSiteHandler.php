<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Queries\FindActiveSiteQuery;
use App\Infrastructure\Persistence\Eloquent\SiteModel;

/**
 * Handler para buscar un sitio activo por ID.
 */
class FindActiveSiteHandler
{
    public function handle(FindActiveSiteQuery $query): ?SiteModel
    {
        return SiteModel::where('id', $query->siteId)
            ->where('is_active', true)
            ->first();
    }
}
