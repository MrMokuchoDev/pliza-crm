<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Commands\ToggleSiteActiveCommand;
use App\Infrastructure\Persistence\Eloquent\SiteModel;

/**
 * Handler para activar/desactivar un sitio.
 */
class ToggleSiteActiveHandler
{
    /**
     * @return array{success: bool, is_active?: bool}
     */
    public function handle(ToggleSiteActiveCommand $command): array
    {
        $site = SiteModel::find($command->siteId);

        if (! $site) {
            return ['success' => false];
        }

        $site->update(['is_active' => ! $site->is_active]);

        return [
            'success' => true,
            'is_active' => $site->is_active,
        ];
    }
}
