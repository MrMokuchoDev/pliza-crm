<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Lead\Commands\CreateLeadCommand;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use Illuminate\Support\Facades\Auth;

/**
 * Handler para crear un nuevo Lead.
 */
class CreateLeadHandler
{
    public function handle(CreateLeadCommand $command): LeadModel
    {
        // Obtener custom fields directamente del DTO
        $customFields = $command->data->customFields;

        // Auto-asignar al vendedor que crea el lead si no tiene asignación
        $assignedTo = $command->data->assignedTo;
        if (empty($assignedTo)) {
            $user = Auth::user();
            if ($user && $user->isSales()) {
                $assignedTo = $user->uuid;
            }
        }

        // Campos regulares del sistema (NO incluir los que son custom fields)
        $regularFields = array_filter([
            'source_type' => $command->data->sourceType?->value,
            'source_site_id' => $command->data->sourceSiteId,
            'source_url' => $command->data->sourceUrl,
            'metadata' => $command->data->metadata,
            'assigned_to' => $assignedTo,
        ], fn($value) => $value !== null);

        // Crear el lead con campos regulares y custom fields
        $lead = LeadModel::create($regularFields);

        // Asignar custom fields usando helper del trait
        $lead->setCustomFieldsFromArray($customFields)->save();

        return $lead;
    }
}
