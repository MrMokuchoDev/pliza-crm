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

        $lead->update($command->data->toArray());

        return $lead->fresh();
    }
}
