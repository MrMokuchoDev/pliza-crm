<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Commands;

/**
 * Comando para reordenar las fases de venta.
 */
readonly class ReorderPhasesCommand
{
    /**
     * @param  array<int, string>  $orderedIds  Lista de IDs en el nuevo orden
     */
    public function __construct(
        public array $orderedIds,
    ) {}
}
