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
        $data = $command->data->toArray();

        // Auto-asignar al vendedor que crea el lead si no tiene asignación
        if (empty($data['assigned_to'])) {
            $user = Auth::user();
            if ($user && $user->isSales()) {
                $data['assigned_to'] = $user->uuid;
            }
        }

        // Separar custom fields de campos normales
        $customFieldMapping = (new LeadModel())->getCustomFieldMapping();
        $customFields = [];
        $regularFields = [];

        foreach ($data as $key => $value) {
            if (isset($customFieldMapping[$key])) {
                $customFields[$key] = $value;
            } else {
                $regularFields[$key] = $value;
            }
        }

        // Crear el lead con campos regulares
        $lead = LeadModel::create($regularFields);

        // Asignar custom fields manualmente
        foreach ($customFields as $key => $value) {
            $lead->$key = $value;
        }

        // Guardar custom fields
        $lead->saveCustomFieldValues();

        return $lead;
    }
}
