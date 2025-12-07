<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Lead\Commands\CreateLeadCommand;
use App\Infrastructure\Persistence\Eloquent\LeadModel;

/**
 * Handler para crear un nuevo Lead.
 */
class CreateLeadHandler
{
    public function handle(CreateLeadCommand $command): LeadModel
    {
        return LeadModel::create($command->data->toArray());
    }
}
