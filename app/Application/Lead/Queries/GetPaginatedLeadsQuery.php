<?php

declare(strict_types=1);

namespace App\Application\Lead\Queries;

/**
 * Query para obtener leads paginados con filtros.
 */
readonly class GetPaginatedLeadsQuery
{
    /**
     * @param  array{search?: string, source?: string}  $filters
     */
    public function __construct(
        public array $filters = [],
        public int $perPage = 10,
    ) {}
}
