<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Handlers;

use App\Application\SalePhase\Commands\CreateSalePhaseCommand;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;

/**
 * Handler para crear una nueva fase de venta.
 */
class CreateSalePhaseHandler
{
    public function handle(CreateSalePhaseCommand $command): SalePhaseModel
    {
        $data = $command->data;

        // Obtener el siguiente orden si no se especifica
        $order = $data->order ?? (SalePhaseModel::max('order') ?? 0) + 1;

        // Si es fase cerrada pero no se especifica isWon, default a false
        $isWon = $data->isClosed ? ($data->isWon ?? false) : false;

        return SalePhaseModel::create([
            'name' => $data->name,
            'color' => $data->color ?? '#6B7280',
            'order' => $order,
            'is_closed' => $data->isClosed ?? false,
            'is_won' => $isWon,
            'is_default' => $data->isDefault ?? false,
        ]);
    }
}
