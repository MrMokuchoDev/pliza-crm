<?php

declare(strict_types=1);

namespace App\Application\Site\Services;

use App\Application\Site\Commands\CreateSiteCommand;
use App\Application\Site\Commands\DeleteSiteCommand;
use App\Application\Site\Commands\RegenerateApiKeyCommand;
use App\Application\Site\Commands\ToggleSiteActiveCommand;
use App\Application\Site\Commands\UpdateSiteCommand;
use App\Application\Site\DTOs\SiteData;
use App\Application\Site\Handlers\CreateSiteHandler;
use App\Application\Site\Handlers\DeleteSiteHandler;
use App\Application\Site\Handlers\RegenerateApiKeyHandler;
use App\Application\Site\Handlers\ToggleSiteActiveHandler;
use App\Application\Site\Handlers\UpdateSiteHandler;
use App\Infrastructure\Persistence\Eloquent\SiteModel;
use Illuminate\Support\Collection;

/**
 * Servicio de aplicación para gestionar Sitios Web.
 * Orquesta los comandos y handlers.
 */
class SiteService
{
    public function __construct(
        private readonly CreateSiteHandler $createHandler,
        private readonly UpdateSiteHandler $updateHandler,
        private readonly DeleteSiteHandler $deleteHandler,
        private readonly ToggleSiteActiveHandler $toggleActiveHandler,
        private readonly RegenerateApiKeyHandler $regenerateApiKeyHandler,
    ) {}

    /**
     * Crear un nuevo sitio.
     */
    public function create(SiteData $data): SiteModel
    {
        $command = new CreateSiteCommand($data);

        return $this->createHandler->handle($command);
    }

    /**
     * Actualizar un sitio existente.
     */
    public function update(string $siteId, SiteData $data): ?SiteModel
    {
        $command = new UpdateSiteCommand($siteId, $data);

        return $this->updateHandler->handle($command);
    }

    /**
     * Eliminar un sitio.
     *
     * @return array{success: bool, error?: string}
     */
    public function delete(string $siteId): array
    {
        $command = new DeleteSiteCommand($siteId);

        return $this->deleteHandler->handle($command);
    }

    /**
     * Activar/desactivar un sitio.
     *
     * @return array{success: bool, is_active?: bool}
     */
    public function toggleActive(string $siteId): array
    {
        $command = new ToggleSiteActiveCommand($siteId);

        return $this->toggleActiveHandler->handle($command);
    }

    /**
     * Regenerar API key de un sitio.
     *
     * @return array{success: bool, api_key?: string}
     */
    public function regenerateApiKey(string $siteId): array
    {
        $command = new RegenerateApiKeyCommand($siteId);

        return $this->regenerateApiKeyHandler->handle($command);
    }

    /**
     * Buscar un sitio por ID.
     */
    public function find(string $siteId): ?SiteModel
    {
        return SiteModel::find($siteId);
    }

    /**
     * Obtener todos los sitios ordenados por fecha de creación.
     *
     * @return Collection<int, SiteModel>
     */
    public function getAllOrdered(): Collection
    {
        return SiteModel::orderBy('created_at', 'desc')->get();
    }

    /**
     * Obtener solo sitios activos.
     *
     * @return Collection<int, SiteModel>
     */
    public function getActiveSites(): Collection
    {
        return SiteModel::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Buscar sitio por API key.
     */
    public function findByApiKey(string $apiKey): ?SiteModel
    {
        return SiteModel::where('api_key', $apiKey)->first();
    }

    /**
     * Contar leads de un sitio.
     */
    public function getLeadsCount(string $siteId): int
    {
        $site = SiteModel::find($siteId);

        return $site ? $site->leads()->count() : 0;
    }
}
