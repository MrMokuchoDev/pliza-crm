<?php

declare(strict_types=1);

namespace App\Application\Site\Queries;

final class GetSiteStatisticsQuery
{
    public function __construct(
        public readonly string $siteId,
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
    ) {}
}
