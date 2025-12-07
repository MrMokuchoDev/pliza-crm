<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Commands;

use App\Application\SalePhase\DTOs\SalePhaseData;

/**
 * Comando para actualizar una fase de venta existente.
 */
readonly class UpdateSalePhaseCommand
{
    public function __construct(
        public string $phaseId,
        public SalePhaseData $data,
    ) {}
}
