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

        $deal->update($command->data->toArrayForUpdate());

        return $deal->fresh();
    }
}
