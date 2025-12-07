<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Commands;

use App\Application\SalePhase\DTOs\SalePhaseData;

/**
 * Comando para crear una nueva fase de venta.
 */
readonly class CreateSalePhaseCommand
{
    public function __construct(
        public SalePhaseData $data,
    ) {}
}
