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

        $data = $command->data->toArrayForUpdate();

        // Separar custom fields de campos normales
        $customFieldMapping = $deal->getCustomFieldMapping();
        $customFields = [];
        $regularFields = [];

        foreach ($data as $key => $value) {
            if (isset($customFieldMapping[$key])) {
                $customFields[$key] = $value;
            } else {
                $regularFields[$key] = $value;
            }
        }

        // Actualizar campos regulares
        $deal->update($regularFields);

        // Asignar y guardar custom fields
        foreach ($customFields as $key => $value) {
            $deal->$key = $value;
        }
        $deal->saveCustomFieldValues();

        return $deal->fresh();
    }
}
