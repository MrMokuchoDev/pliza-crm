<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Handlers;

use App\Application\SalePhase\Commands\UpdateSalePhaseCommand;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;

/**
 * Handler para actualizar una fase de venta.
 */
class UpdateSalePhaseHandler
{
    public function handle(UpdateSalePhaseCommand $command): ?SalePhaseModel
    {
        $phase = SalePhaseModel::find($command->phaseId);

        if (! $phase) {
            return null;
        }

        $data = $command->data->toArray();

        // Si se actualiza is_closed a false, is_won debe ser false
        if (isset($data['is_closed']) && ! $data['is_closed']) {
            $data['is_won'] = false;
        }

        $phase->update($data);

        return $phase->fresh();
    }
}
