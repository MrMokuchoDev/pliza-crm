<?php

declare(strict_types=1);

namespace App\Application\Lead\Services;

use App\Application\Lead\Commands\CaptureLeadCommand;
use App\Application\Lead\Commands\CreateLeadCommand;
use App\Application\Lead\Commands\DeleteLeadCommand;
use App\Application\Lead\Commands\UpdateLeadCommand;
use App\Application\Lead\DTOs\LeadData;
use App\Application\Lead\Handlers\CaptureLeadHandler;
use App\Application\Lead\Handlers\CreateLeadHandler;
use App\Application\Lead\Handlers\DeleteLeadHandler;
use App\Application\Lead\Handlers\FindLeadByContactHandler;
use App\Application\Lead\Handlers\FindLeadHandler;
use App\Application\Lead\Handlers\GetLeadRelatedCountsHandler;
use App\Application\Lead\Handlers\GetLeadStatsHandler;
use App\Application\Lead\Handlers\GetPaginatedLeadsHandler;
use App\Application\Lead\Handlers\SearchLeadsHandler;
use App\Application\Lead\Handlers\UpdateLeadHandler;
use App\Application\Lead\Queries\FindLeadByContactQuery;
use App\Application\Lead\Queries\FindLeadQuery;
use App\Application\Lead\Queries\GetLeadRelatedCountsQuery;
use App\Application\Lead\Queries\GetLeadStatsQuery;
use App\Application\Lead\Queries\GetPaginatedLeadsQuery;
use App\Application\Lead\Queries\SearchLeadsQuery;
use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Servicio de aplicación para gestionar Leads.
 * Orquesta los comandos, queries y handlers.
 */
class LeadService
{
    public function __construct(
        private readonly CreateLeadHandler $createHandler,
        private readonly UpdateLeadHandler $updateHandler,
        private readonly DeleteLeadHandler $deleteHandler,
        private readonly FindLeadHandler $findHandler,
        private readonly SearchLeadsHandler $searchHandler,
        private readonly GetPaginatedLeadsHandler $paginatedHandler,
        private readonly GetLeadStatsHandler $statsHandler,
        private readonly GetLeadRelatedCountsHandler $relatedCountsHandler,
        private readonly FindLeadByContactHandler $findByContactHandler,
        private readonly CaptureLeadHandler $captureHandler,
    ) {}

    /**
     * Crear un nuevo Lead.
     */
    public function create(LeadData $data): LeadModel
    {
        $command = new CreateLeadCommand($data);

        return $this->createHandler->handle($command);
    }

    /**
     * Actualizar un Lead existente.
     */
    public function update(string $leadId, LeadData $data): ?LeadModel
    {
        $command = new UpdateLeadCommand($leadId, $data);

        return $this->updateHandler->handle($command);
    }

    /**
     * Eliminar un Lead y sus relaciones en cascada.
     *
     * @return array{success: bool, deleted: array{comments: int, deals: int, notes: int}}
     */
    public function delete(string $leadId): array
    {
        $command = new DeleteLeadCommand($leadId);

        return $this->deleteHandler->handle($command);
    }

    /**
     * Buscar un Lead por ID.
     */
    public function find(string $leadId): ?LeadModel
    {
        $query = new FindLeadQuery($leadId);

        return $this->findHandler->handle($query);
    }

    /**
     * Buscar un Lead con todas sus relaciones.
     */
    public function findWithRelations(string $leadId): ?LeadModel
    {
        $query = new FindLeadQuery($leadId, withRelations: true);

        return $this->findHandler->handle($query);
    }

    /**
     * Buscar un Lead y verificar si tiene negocio abierto.
     *
     * @return array{lead: LeadModel|null, has_open_deal: bool}
     */
    public function findWithOpenDealCheck(string $leadId): array
    {
        $lead = $this->find($leadId);

        return [
            'lead' => $lead,
            'has_open_deal' => $lead?->hasOpenDeal() ?? false,
        ];
    }

    /**
     * Verificar si un Lead existe.
     */
    public function exists(string $leadId): bool
    {
        return $this->find($leadId) !== null;
    }

    /**
     * Obtener nombre para mostrar del Lead.
     */
    public function getDisplayName(string $leadId): string
    {
        $lead = $this->find($leadId);

        if (! $lead) {
            return 'Sin nombre';
        }

        return $lead->name ?? $lead->email ?? $lead->phone ?? 'Sin nombre';
    }

    /**
     * Obtener conteos de relaciones para mostrar en confirmación de eliminación.
     *
     * @return array{deals: int, notes: int}
     */
    public function getRelatedCounts(string $leadId): array
    {
        $query = new GetLeadRelatedCountsQuery($leadId);

        return $this->relatedCountsHandler->handle($query);
    }

    /**
     * Buscar leads por término de búsqueda.
     *
     * @param  string  $term  Término de búsqueda
     * @param  int  $limit  Límite de resultados
     * @param  string|null  $userUuid  UUID del usuario para filtrar por asignación
     * @param  bool  $onlyOwn  Si true, solo muestra leads asignados al usuario
     * @return Collection<int, LeadModel>
     */
    public function search(
        string $term,
        int $limit = 10,
        ?string $userUuid = null,
        bool $onlyOwn = false,
    ): Collection {
        $query = new SearchLeadsQuery($term, $limit, $userUuid, $onlyOwn);

        return $this->searchHandler->handle($query);
    }

    /**
     * Obtener todos los leads paginados con filtros.
     *
     * @param  array{search?: string, source?: string, assigned_to?: string}  $filters
     * @param  string|null  $userUuid  UUID del usuario para filtrar por asignación
     * @param  bool  $onlyOwn  Si true, solo muestra leads asignados al usuario
     */
    public function getPaginated(
        array $filters = [],
        int $perPage = 10,
        ?string $userUuid = null,
        bool $onlyOwn = false,
    ): LengthAwarePaginator {
        $query = new GetPaginatedLeadsQuery($filters, $perPage, $userUuid, $onlyOwn);

        return $this->paginatedHandler->handle($query);
    }

    /**
     * Obtener estadísticas de leads.
     *
     * @return array{total: int, with_deals: int, without_deals: int}
     */
    public function getStats(): array
    {
        $query = new GetLeadStatsQuery();

        return $this->statsHandler->handle($query);
    }

    /**
     * Buscar un Lead por email o teléfono.
     */
    public function findByContact(?string $email = null, ?string $phone = null, bool $lockForUpdate = false): ?LeadModel
    {
        $query = new FindLeadByContactQuery($email, $phone, $lockForUpdate);

        return $this->findByContactHandler->handle($query);
    }

    /**
     * Capturar un Lead desde un widget externo.
     *
     * @return array{success: bool, message: string, data: array, status_code: int}
     */
    public function capture(
        string $siteId,
        SourceType $sourceType,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $message = null,
        ?string $sourceUrl = null,
        ?string $userAgent = null,
        ?string $ipAddress = null,
        ?string $pageUrl = null,
    ): array {
        $command = new CaptureLeadCommand(
            siteId: $siteId,
            sourceType: $sourceType,
            name: $name,
            email: $email,
            phone: $phone,
            message: $message,
            sourceUrl: $sourceUrl,
            userAgent: $userAgent,
            ipAddress: $ipAddress,
            pageUrl: $pageUrl,
        );

        return $this->captureHandler->handle($command);
    }
}
