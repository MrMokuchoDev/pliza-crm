<?php

declare(strict_types=1);

namespace App\Application\Deal\Queries;

/**
 * Query para obtener deals por fase (para Kanban).
 */
readonly class GetDealsByPhaseQuery
{
    /**
     * @param  array<string>  $phaseIds
     * @param  string|null  $userUuid  UUID del usuario para filtrar por asignación
     * @param  bool  $onlyOwn  Si true, solo muestra deals asignados al usuario
     */
    public function __construct(
        public array $phaseIds,
        public ?string $search = null,
        public ?string $userUuid = null,
        public bool $onlyOwn = false,
    ) {}
}
