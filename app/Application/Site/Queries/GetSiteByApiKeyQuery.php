<?php

declare(strict_types=1);

namespace App\Application\Site\Queries;

final class GetSiteByApiKeyQuery
{
    public function __construct(
        public readonly string $apiKey,
    ) {}
}
