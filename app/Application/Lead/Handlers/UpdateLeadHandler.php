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

        // Obtener custom fields directamente del DTO
        $customFields = $command->data->customFields;

        // Campos regulares del sistema (NO incluir los que son custom fields)
        $regularFields = array_filter([
            'source_type' => $command->data->sourceType?->value,
            'source_site_id' => $command->data->sourceSiteId,
            'source_url' => $command->data->sourceUrl,
            'metadata' => $command->data->metadata,
            'assigned_to' => $command->data->assignedTo,
        ], fn($value) => $value !== null);

        // Actualizar campos regulares
        $lead->update($regularFields);

        // Asignar custom fields usando helper del trait
        $lead->setCustomFieldsFromArray($customFields)->save();

        return $lead->fresh();
    }
}
