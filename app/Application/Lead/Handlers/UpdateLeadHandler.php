<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Lead\Commands\UpdateLeadCommand;
use App\Infrastructure\Persistence\Eloquent\LeadModel;

/**
 * Handler para actualizar un Lead existente.
 */
class UpdateLeadHandler
{
    public function handle(UpdateLeadCommand $command): ?LeadModel
    {
        $lead = LeadModel::find($command->leadId);

        if (! $lead) {
            return null;
        }

        $data = $command->data->toArray();

        // Separar custom fields de campos normales
        $customFieldMapping = $lead->getCustomFieldMapping();
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
        $lead->update($regularFields);

        // Asignar y guardar custom fields
        foreach ($customFields as $key => $value) {
            $lead->$key = $value;
        }
        $lead->saveCustomFieldValues();

        return $lead->fresh();
    }
}
