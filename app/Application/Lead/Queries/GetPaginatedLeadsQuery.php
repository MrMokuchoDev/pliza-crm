<?php

declare(strict_types=1);

namespace App\Application\Lead\Queries;

/**
 * Query para obtener leads paginados con filtros.
 */
readonly class GetPaginatedLeadsQuery
{
    /**
     * @param  array{search?: string, source?: string, assigned_to?: string}  $filters
     * @param  string|null  $userUuid  UUID del usuario para filtrar por asignación
     * @param  bool  $onlyOwn  Si true, solo muestra leads asignados al usuario
     */
    public function __construct(
        public array $filters = [],
        public int $perPage = 10,
        public ?string $userUuid = null,
        public bool $onlyOwn = false,
    ) {}
}
