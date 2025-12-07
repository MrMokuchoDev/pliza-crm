<?php

declare(strict_types=1);

namespace App\Application\Deal\Handlers;

use App\Application\Deal\Commands\CreateDealCommand;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
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

            // Si no hay leadId pero hay datos de lead, crear el lead primero
            if (! $leadId && $command->leadData) {
                $lead = LeadModel::create($command->leadData);
                $leadId = $lead->id;
                $leadCreated = true;
            }

            $dealData = $command->dealData->toArray();
            $dealData['lead_id'] = $leadId;

            $deal = DealModel::create($dealData);

            return [
                'deal' => $deal,
                'lead_created' => $leadCreated,
            ];
        });
    }
}
