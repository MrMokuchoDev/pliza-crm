<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Queries;

/**
 * Query para obtener la fase por defecto.
 */
readonly class GetDefaultPhaseQuery
{
    /**
     * @param  bool  $fallbackToFirstOpen  Si no hay fase por defecto, retornar la primera abierta
     */
    public function __construct(
        public bool $fallbackToFirstOpen = false,
    ) {}
}
