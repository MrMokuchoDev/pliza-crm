<?php

declare(strict_types=1);

namespace App\Application\Site\Queries;

/**
 * Query para buscar un sitio activo por ID.
 */
readonly class FindActiveSiteQuery
{
    public function __construct(
        public string $siteId,
    ) {}
}
