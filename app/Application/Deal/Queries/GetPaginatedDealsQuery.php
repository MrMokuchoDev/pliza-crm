<?php

declare(strict_types=1);

namespace App\Application\Deal\Queries;

/**
 * Query para obtener deals paginados con filtros.
 */
readonly class GetPaginatedDealsQuery
{
    /**
     * @param  array{search?: string, phase_id?: string, source_type?: string}  $filters
     */
    public function __construct(
        public array $filters = [],
        public int $perPage = 10,
    ) {}
}
