<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Commands\CreateSiteCommand;
use App\Infrastructure\Persistence\Eloquent\SiteModel;
use Illuminate\Support\Str;

/**
 * Handler para crear un nuevo sitio web.
 */
class CreateSiteHandler
{
    public function handle(CreateSiteCommand $command): SiteModel
    {
        $data = $command->data;

        return SiteModel::create([
            'name' => $data->name,
            'domain' => $data->domain,
            'api_key' => $this->generateApiKey(),
            'is_active' => $data->isActive ?? true,
            'default_user_id' => $data->defaultUserId,
            'round_robin_index' => 0,
            'settings' => $data->settings ?? [],
            'created_at' => now(),
        ]);
    }

    private function generateApiKey(): string
    {
        return 'sk_' . Str::random(32);
    }
}
