<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Queries\GetSiteByIdQuery;
use App\Infrastructure\Persistence\Eloquent\SiteModel;

final class GetSiteByIdHandler
{
    public function handle(GetSiteByIdQuery $query): ?SiteModel
    {
        return SiteModel::find($query->siteId);
    }
}
