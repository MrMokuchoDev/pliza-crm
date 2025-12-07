<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Commands\RegenerateApiKeyCommand;
use App\Infrastructure\Persistence\Eloquent\SiteModel;
use Illuminate\Support\Str;

/**
 * Handler para regenerar la API key de un sitio.
 */
class RegenerateApiKeyHandler
{
    /**
     * @return array{success: bool, api_key?: string}
     */
    public function handle(RegenerateApiKeyCommand $command): array
    {
        $site = SiteModel::find($command->siteId);

        if (! $site) {
            return ['success' => false];
        }

        $newApiKey = $this->generateApiKey();
        $site->update(['api_key' => $newApiKey]);

        return [
            'success' => true,
            'api_key' => $newApiKey,
        ];
    }

    private function generateApiKey(): string
    {
        return 'sk_' . Str::random(32);
    }
}
