<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Queries\GetSiteLeadsCountQuery;
use App\Infrastructure\Persistence\Eloquent\SiteModel;

final class GetSiteLeadsCountHandler
{
    public function handle(GetSiteLeadsCountQuery $query): int
    {
        $site = SiteModel::find($query->siteId);

        return $site ? $site->leads()->count() : 0;
    }
}
