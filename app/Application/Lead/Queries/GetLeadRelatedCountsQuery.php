<?php

declare(strict_types=1);

namespace App\Application\Lead\Queries;

/**
 * Query para obtener conteos de relaciones de un Lead.
 */
readonly class GetLeadRelatedCountsQuery
{
    public function __construct(
        public string $leadId,
    ) {}
}
