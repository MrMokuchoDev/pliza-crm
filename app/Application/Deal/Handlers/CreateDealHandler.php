<?php

declare(strict_types=1);

namespace App\Application\Deal\Handlers;

use App\Application\Deal\Commands\CreateDealCommand;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Handler para crear un nuevo Deal.
 * Puede crear un Lead nuevo junto con el Deal si se proporcionan datos.
 */
class CreateDealHandler
{
    /**
     * @return array{deal: DealModel, lead_created: bool}
     */
    public function handle(CreateDealCommand $command): array
    {
        $leadCreated = false;

        return DB::transaction(function () use ($command, &$leadCreated) {
            $leadId = $command->dealData->leadId;
            $user = Auth::user();

            // Si no hay leadId pero hay datos de lead, crear el lead primero
            if (! $leadId && $command->leadData) {
                $leadData = $command->leadData;

                // Auto-asignar al vendedor si no tiene asignación
                if (empty($leadData['assigned_to']) && $user && $user->isSales()) {
                    $leadData['assigned_to'] = $user->uuid;
                }

                $lead = LeadModel::create($leadData);
                $leadId = $lead->id;
                $leadCreated = true;
            }

            // Obtener custom fields directamente del DTO
            $customFields = $command->dealData->customFields;

            // Auto-asignar al vendedor si no tiene asignación
            $assignedTo = $command->dealData->assignedTo;
            if (empty($assignedTo) && $user && $user->isSales()) {
                $assignedTo = $user->uuid;
            }

            // Campos regulares del sistema (NO incluir los que son custom fields)
            $regularFields = array_filter([
                'lead_id' => $leadId,
                'sale_phase_id' => $command->dealData->salePhaseId,
                'close_date' => $command->dealData->closeDate,
                'assigned_to' => $assignedTo,
                'created_by' => $user?->uuid,
            ], fn($value) => $value !== null);

            // Crear el deal con campos regulares y custom fields
            $deal = DealModel::create($regularFields);

            // Asignar custom fields usando helper del trait
            $deal->setCustomFieldsFromArray($customFields)->save();

            return [
                'deal' => $deal,
                'lead_created' => $leadCreated,
            ];
        });
    }
}
