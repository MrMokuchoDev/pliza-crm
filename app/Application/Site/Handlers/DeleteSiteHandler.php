<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Commands\DeleteSiteCommand;
use App\Infrastructure\Persistence\Eloquent\SiteModel;

/**
 * Handler para eliminar un sitio web.
 *
 * No permite eliminar si tiene leads asociados.
 */
class DeleteSiteHandler
{
    /**
     * @return array{success: bool, error?: string}
     */
    public function handle(DeleteSiteCommand $command): array
    {
        $site = SiteModel::find($command->siteId);

        if (! $site) {
            return ['success' => false, 'error' => 'El sitio no existe'];
        }

        $leadsCount = $site->leads()->count();

        if ($leadsCount > 0) {
            return [
                'success' => false,
                'error' => "No se puede eliminar: tiene {$leadsCount} leads asociados",
            ];
        }

        $site->delete();

        return ['success' => true];
    }
}
