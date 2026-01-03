<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Commands\CreateSiteCommand;
use App\Domain\Site\Services\PrivacyPolicyValidator;
use App\Infrastructure\Persistence\Eloquent\SiteModel;
use Illuminate\Support\Str;

/**
 * Handler para crear un nuevo sitio web.
 */
class CreateSiteHandler
{
    public function __construct(
        private readonly PrivacyPolicyValidator $privacyValidator,
    ) {}

    public function handle(CreateSiteCommand $command): SiteModel
    {
        $data = $command->data;

        // Validar URL de privacidad si se proporciona
        if ($data->privacyPolicyUrl !== null && $data->privacyPolicyUrl !== '') {
            $this->privacyValidator->validate($data->privacyPolicyUrl, $data->domain);
        }

        return SiteModel::create([
            'name' => $data->name,
            'domain' => $data->domain,
            'api_key' => $this->generateApiKey(),
            'is_active' => $data->isActive ?? true,
            'default_user_id' => $data->defaultUserId,
            'round_robin_index' => 0,
            'settings' => $data->settings ?? [],
            'privacy_policy_url' => $data->privacyPolicyUrl,
            'created_at' => now(),
        ]);
    }

    private function generateApiKey(): string
    {
        return 'sk_' . Str::random(32);
    }
}
