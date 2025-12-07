<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Commands\UpdateSiteCommand;
use App\Infrastructure\Persistence\Eloquent\SiteModel;

/**
 * Handler para actualizar un sitio web.
 */
class UpdateSiteHandler
{
    public function handle(UpdateSiteCommand $command): ?SiteModel
    {
        $site = SiteModel::find($command->siteId);

        if (! $site) {
            return null;
        }

        $data = $command->data->toArray();

        // No permitir actualizar api_key directamente (usar RegenerateApiKey)
        unset($data['api_key']);

        $site->update($data);

        return $site->fresh();
    }
}
