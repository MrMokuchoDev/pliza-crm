<?php

declare(strict_types=1);

namespace App\Application\Lead\Queries;

/**
 * Query para buscar un Lead por ID.
 */
readonly class FindLeadQuery
{
    public function __construct(
        public string $leadId,
        public bool $withRelations = false,
    ) {}
}
