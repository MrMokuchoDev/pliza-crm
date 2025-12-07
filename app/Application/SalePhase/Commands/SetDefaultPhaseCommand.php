<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Commands;

/**
 * Comando para establecer una fase como la fase por defecto.
 */
readonly class SetDefaultPhaseCommand
{
    public function __construct(
        public string $phaseId,
    ) {}
}
