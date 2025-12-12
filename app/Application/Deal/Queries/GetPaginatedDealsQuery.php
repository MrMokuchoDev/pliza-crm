<?php

declare(strict_types=1);

namespace App\Application\Deal\Queries;

/**
 * Query para obtener deals paginados con filtros.
 */
readonly class GetPaginatedDealsQuery
{
    /**
     * @param  array{search?: string, phase_id?: string, source_type?: string, assigned_to?: string}  $filters
     * @param  string|null  $userUuid  UUID del usuario para filtrar por asignación
     * @param  bool  $onlyOwn  Si true, solo muestra deals asignados al usuario
     */
    public function __construct(
        public array $filters = [],
        public int $perPage = 10,
        public ?string $userUuid = null,
        public bool $onlyOwn = false,
    ) {}
}
