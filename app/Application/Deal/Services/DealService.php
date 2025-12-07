<?php

declare(strict_types=1);

namespace App\Application\Deal\Services;

use App\Application\Deal\Commands\CreateDealCommand;
use App\Application\Deal\Commands\DeleteDealCommand;
use App\Application\Deal\Commands\UpdateDealCommand;
use App\Application\Deal\DTOs\DealData;
use App\Application\Deal\Handlers\CountDealsByLeadHandler;
use App\Application\Deal\Handlers\CountDealsByPhaseHandler;
use App\Application\Deal\Handlers\CreateDealHandler;
use App\Application\Deal\Handlers\DeleteDealHandler;
use App\Application\Deal\Handlers\FindDealHandler;
use App\Application\Deal\Handlers\GetDealsByPhaseHandler;
use App\Application\Deal\Handlers\GetDealStatsHandler;
use App\Application\Deal\Handlers\GetPaginatedDealsHandler;
use App\Application\Deal\Handlers\UpdateDealHandler;
use App\Application\Deal\Queries\CountDealsByLeadQuery;
use App\Application\Deal\Queries\CountDealsByPhaseQuery;
use App\Application\Deal\Queries\FindDealQuery;
use App\Application\Deal\Queries\GetDealsByPhaseQuery;
use App\Application\Deal\Queries\GetDealStatsQuery;
use App\Application\Deal\Queries\GetPaginatedDealsQuery;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Servicio de aplicación para gestionar Deals.
 * Orquesta los comandos, queries y handlers.
 */
class DealService
{
    public function __construct(
        private readonly CreateDealHandler $createHandler,
        private readonly UpdateDealHandler $updateHandler,
        private readonly DeleteDealHandler $deleteHandler,
        private readonly FindDealHandler $findHandler,
        private readonly GetDealsByPhaseHandler $dealsByPhaseHandler,
        private readonly GetPaginatedDealsHandler $paginatedHandler,
        private readonly GetDealStatsHandler $statsHandler,
        private readonly CountDealsByLeadHandler $countByLeadHandler,
        private readonly CountDealsByPhaseHandler $countByPhaseHandler,
    ) {}

    /**
     * Crear un nuevo Deal.
     *
     * @param  array<string, mixed>|null  $leadData  Datos opcionales para crear Lead junto con el Deal
     * @return array{deal: DealModel, lead_created: bool}
     */
    public function create(DealData $dealData, ?array $leadData = null): array
    {
        $command = new CreateDealCommand($dealData, $leadData);

        return $this->createHandler->handle($command);
    }

    /**
     * Actualizar un Deal existente.
     */
    public function update(string $dealId, DealData $data): ?DealModel
    {
        $command = new UpdateDealCommand($dealId, $data);

        return $this->updateHandler->handle($command);
    }

    /**
     * Eliminar un Deal y sus comentarios.
     *
     * @return array{success: bool, deleted_comments: int}
     */
    public function delete(string $dealId): array
    {
        $command = new DeleteDealCommand($dealId);

        return $this->deleteHandler->handle($command);
    }

    /**
     * Buscar un Deal por ID.
     */
    public function find(string $dealId): ?DealModel
    {
        $query = new FindDealQuery($dealId);

        return $this->findHandler->handle($query);
    }

    /**
     * Obtener deal con lead cargado (para formularios).
     */
    public function findWithLead(string $dealId): ?DealModel
    {
        $query = new FindDealQuery($dealId, FindDealQuery::WITH_LEAD);

        return $this->findHandler->handle($query);
    }

    /**
     * Obtener deal con relaciones cargadas para cambio de fase.
     */
    public function findWithRelations(string $dealId): ?DealModel
    {
        $query = new FindDealQuery($dealId, FindDealQuery::WITH_ALL);

        return $this->findHandler->handle($query);
    }

    /**
     * Obtener deal con todas las relaciones (para vista detalle).
     */
    public function findForShow(string $dealId): ?DealModel
    {
        $query = new FindDealQuery($dealId, FindDealQuery::WITH_ALL);

        return $this->findHandler->handle($query);
    }

    /**
     * Obtener conteo de comentarios para mostrar en confirmación de eliminación.
     */
    public function getCommentsCount(string $dealId): int
    {
        $deal = DealModel::withCount('comments')->find($dealId);

        return $deal?->comments_count ?? 0;
    }

    /**
     * Obtener nombre para mostrar del Deal.
     */
    public function getDisplayName(string $dealId): string
    {
        $deal = $this->find($dealId);

        if (! $deal) {
            return 'Sin nombre';
        }

        return $deal->name ?? 'Negocio sin nombre';
    }

    /**
     * Cambiar la fase de un Deal.
     */
    public function changePhase(string $dealId, string $newPhaseId, ?float $value = null, ?string $closeDate = null): ?DealModel
    {
        $data = DealData::fromArray([
            'sale_phase_id' => $newPhaseId,
            'value' => $value,
            'close_date' => $closeDate,
        ]);

        return $this->update($dealId, $data);
    }

    /**
     * Obtener IDs de deals de un Lead.
     *
     * @return SupportCollection<int, string>
     */
    public function getDealIdsByLeadId(string $leadId): SupportCollection
    {
        return DealModel::where('lead_id', $leadId)->pluck('id');
    }

    /**
     * Contar deals de un Lead.
     */
    public function countByLeadId(string $leadId): int
    {
        $query = new CountDealsByLeadQuery($leadId);

        return $this->countByLeadHandler->handle($query);
    }

    /**
     * Eliminar todos los deals de un Lead.
     */
    public function deleteByLeadId(string $leadId): int
    {
        $count = $this->countByLeadId($leadId);
        DealModel::where('lead_id', $leadId)->delete();

        return $count;
    }

    /**
     * Contar deals en una fase.
     */
    public function countByPhaseId(string $phaseId): int
    {
        $query = new CountDealsByPhaseQuery($phaseId);

        return $this->countByPhaseHandler->handle($query);
    }

    /**
     * Transferir todos los deals de una fase a otra.
     *
     * @return int Cantidad de deals transferidos
     */
    public function transferToPhase(string $fromPhaseId, string $toPhaseId): int
    {
        $count = $this->countByPhaseId($fromPhaseId);

        DealModel::where('sale_phase_id', $fromPhaseId)
            ->update(['sale_phase_id' => $toPhaseId]);

        return $count;
    }

    /**
     * Obtener deals paginados con filtros.
     *
     * @param  array{search?: string, phase_id?: string, source_type?: string}  $filters
     */
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = new GetPaginatedDealsQuery($filters, $perPage);

        return $this->paginatedHandler->handle($query);
    }

    /**
     * Obtener deals por fase con filtros (para Kanban).
     *
     * @param  array<string>  $phaseIds
     * @return Collection<int, DealModel>
     */
    public function getByPhaseIds(array $phaseIds, ?string $search = null): Collection
    {
        $query = new GetDealsByPhaseQuery($phaseIds, $search);

        return $this->dealsByPhaseHandler->handle($query);
    }

    /**
     * Obtener estadísticas de deals.
     *
     * @param  array<string>|null  $openPhaseIds  IDs de fases abiertas (no cerradas)
     * @return array{total: int, open: int, total_value: float}
     */
    public function getStats(?array $openPhaseIds = null): array
    {
        $query = new GetDealStatsQuery($openPhaseIds);

        return $this->statsHandler->handle($query);
    }
}
