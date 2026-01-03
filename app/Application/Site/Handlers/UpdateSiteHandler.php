<?php

declare(strict_types=1);

namespace App\Application\Site\Handlers;

use App\Application\Site\Commands\UpdateSiteCommand;
use App\Domain\Site\Services\PrivacyPolicyValidator;
use App\Infrastructure\Persistence\Eloquent\SiteModel;

/**
 * Handler para actualizar un sitio web.
 */
class UpdateSiteHandler
{
    public function __construct(
        private readonly PrivacyPolicyValidator $privacyValidator,
    ) {}

    public function handle(UpdateSiteCommand $command): ?SiteModel
    {
        $site = SiteModel::find($command->siteId);

        if (! $site) {
            return null;
        }

        $data = $command->data->toArray();

        // Validar URL de privacidad si se está actualizando
        if (isset($data['privacy_policy_url']) && $data['privacy_policy_url'] !== null && $data['privacy_policy_url'] !== '') {
            // Usar el dominio actualizado si viene en data, sino usar el existente
            $domain = $data['domain'] ?? $site->domain;
            $this->privacyValidator->validate($data['privacy_policy_url'], $domain);
        }

        // No permitir actualizar api_key directamente (usar RegenerateApiKey)
        unset($data['api_key']);

        $site->update($data);

        return $site->fresh();
    }
}
