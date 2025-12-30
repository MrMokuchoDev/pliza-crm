<?php

declare(strict_types=1);

namespace App\Application\Deal\Handlers;

use App\Application\Deal\Commands\UpdateDealCommand;
use App\Infrastructure\Persistence\Eloquent\DealModel;

/**
 * Handler para actualizar un Deal existente.
 */
class UpdateDealHandler
{
    public function handle(UpdateDealCommand $command): ?DealModel
    {
        $deal = DealModel::find($command->dealId);

        if (! $deal) {
            return null;
        }

        // Obtener custom fields directamente del DTO
        $customFields = $command->data->customFields;

        // Campos regulares del sistema (NO incluir los que son custom fields)
        $regularFields = array_filter([
            'sale_phase_id' => $command->data->salePhaseId,
            'close_date' => $command->data->closeDate,
            'assigned_to' => $command->data->assignedTo,
        ], fn($value) => $value !== null);

        // Actualizar campos regulares
        $deal->update($regularFields);

        // Asignar custom fields usando helper del trait
        $deal->setCustomFieldsFromArray($customFields)->save();

        return $deal->fresh();
    }
}
