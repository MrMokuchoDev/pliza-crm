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
     */
    public function __construct(
        public array $phaseIds,
        public ?string $search = null,
    ) {}
}
