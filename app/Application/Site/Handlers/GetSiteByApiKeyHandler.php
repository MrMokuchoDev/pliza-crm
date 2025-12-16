<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Queries\GetSiteByApiKeyQuery;
use App\Infrastructure\Persistence\Eloquent\SiteModel;

final class GetSiteByApiKeyHandler
{
    public function handle(GetSiteByApiKeyQuery $query): ?SiteModel
    {
        return SiteModel::where('api_key', $query->apiKey)->first();
    }
}
