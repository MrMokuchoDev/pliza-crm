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

        return LeadModel::create($data);
    }
}
