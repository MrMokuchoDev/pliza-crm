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
use App\Application\Site\Handlers\FindActiveSiteHandler;
use App\Application\Site\Handlers\GetActiveSitesHandler;
use App\Application\Site\Handlers\GetAllSitesHandler;
use App\Application\Site\Handlers\GetSiteByApiKeyHandler;
use App\Application\Site\Handlers\GetSiteByIdHandler;
use App\Application\Site\Handlers\GetSiteLeadsCountHandler;
use App\Application\Site\Handlers\GetSiteStatisticsHandler;
use App\Application\Site\Handlers\RegenerateApiKeyHandler;
use App\Application\Site\Handlers\ToggleSiteActiveHandler;
use App\Application\Site\Handlers\UpdateSiteHandler;
use App\Application\Site\Queries\FindActiveSiteQuery;
use App\Application\Site\Queries\GetActiveSitesQuery;
use App\Application\Site\Queries\GetAllSitesQuery;
use App\Application\Site\Queries\GetSiteByApiKeyQuery;
use App\Application\Site\Queries\GetSiteByIdQuery;
use App\Application\Site\Queries\GetSiteLeadsCountQuery;
use App\Application\Site\Queries\GetSiteStatisticsQuery;
use App\Infrastructure\Persistence\Eloquent\SiteModel;
use Illuminate\Support\Collection;

/**
 * Servicio de aplicacion para gestionar Sitios Web.
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
        private readonly FindActiveSiteHandler $findActiveSiteHandler,
        private readonly GetSiteStatisticsHandler $statisticsHandler,
        private readonly GetSiteByIdHandler $getSiteByIdHandler,
        private readonly GetAllSitesHandler $getAllSitesHandler,
        private readonly GetActiveSitesHandler $getActiveSitesHandler,
        private readonly GetSiteByApiKeyHandler $getSiteByApiKeyHandler,
        private readonly GetSiteLeadsCountHandler $getSiteLeadsCountHandler,
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
        $query = new GetSiteByIdQuery($siteId);

        return $this->getSiteByIdHandler->handle($query);
    }

    /**
     * Buscar un sitio activo por ID.
     */
    public function findActive(string $siteId): ?SiteModel
    {
        $query = new FindActiveSiteQuery($siteId);

        return $this->findActiveSiteHandler->handle($query);
    }

    /**
     * Obtener todos los sitios ordenados por fecha de creacion.
     *
     * @return Collection<int, SiteModel>
     */
    public function getAllOrdered(): Collection
    {
        $query = new GetAllSitesQuery('created_at', 'desc');

        return $this->getAllSitesHandler->handle($query);
    }

    /**
     * Obtener solo sitios activos.
     *
     * @return Collection<int, SiteModel>
     */
    public function getActiveSites(): Collection
    {
        $query = new GetActiveSitesQuery('created_at', 'desc');

        return $this->getActiveSitesHandler->handle($query);
    }

    /**
     * Buscar sitio por API key.
     */
    public function findByApiKey(string $apiKey): ?SiteModel
    {
        $query = new GetSiteByApiKeyQuery($apiKey);

        return $this->getSiteByApiKeyHandler->handle($query);
    }

    /**
     * Contar leads de un sitio.
     */
    public function getLeadsCount(string $siteId): int
    {
        $query = new GetSiteLeadsCountQuery($siteId);

        return $this->getSiteLeadsCountHandler->handle($query);
    }

    /**
     * Obtener estadisticas de un sitio.
     *
     * @return array{
     *     site: SiteModel|null,
     *     totals: array{leads: int, deals: int, won: int, lost: int, value: float},
     *     conversion_rate: float,
     *     leads_by_source: array,
     *     leads_by_period: array,
     *     deals_by_phase: array,
     *     recent_leads: \Illuminate\Database\Eloquent\Collection
     * }
     */
    public function getStatistics(string $siteId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = new GetSiteStatisticsQuery($siteId, $dateFrom, $dateTo);

        return $this->statisticsHandler->handle($query);
    }
}
