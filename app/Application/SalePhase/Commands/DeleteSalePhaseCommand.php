<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Commands;

/**
 * Comando para eliminar una fase de venta.
 *
 * Si la fase tiene negocios asociados, se deben transferir
 * a otra fase antes de eliminar.
 */
readonly class DeleteSalePhaseCommand
{
    public function __construct(
        public string $phaseId,
        public ?string $transferToPhaseId = null,
    ) {}
}
