<?php

declare(strict_types=1);

namespace App\Application\Site\Queries;

final class GetSiteLeadsCountQuery
{
    public function __construct(
        public readonly string $siteId,
    ) {}
}
