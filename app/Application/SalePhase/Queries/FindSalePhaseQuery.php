<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Queries;

/**
 * Query para buscar una Fase de Venta por ID.
 */
readonly class FindSalePhaseQuery
{
    public function __construct(
        public string $phaseId,
    ) {}
}
