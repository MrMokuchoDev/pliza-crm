<?php

declare(strict_types=1);

namespace App\Application\Deal\Services;

use App\Application\Deal\Commands\CreateDealCommand;
use App\Application\Deal\Commands\DeleteDealCommand;
use App\Application\Deal\Commands\UpdateDealCommand;
use App\Application\Deal\DTOs\DealData;
use App\Application\Deal\Handlers\CreateDealHandler;
use App\Application\Deal\Handlers\DeleteDealHandler;
use App\Application\Deal\Handlers\UpdateDealHandler;
use App\Infrastructure\Persistence\Eloquent\DealModel;

/**
 * Servicio de aplicación para gestionar Deals.
 * Orquesta los comandos y handlers.
 */
class DealService
{
    public function __construct(
        private readonly CreateDealHandler $createHandler,
        private readonly UpdateDealHandler $updateHandler,
        private readonly DeleteDealHandler $deleteHandler,
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
        $deal = DealModel::find($dealId);

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
     * Buscar un Deal por ID.
     */
    public function find(string $dealId): ?DealModel
    {
        return DealModel::find($dealId);
    }

    /**
     * Obtener IDs de deals de un Lead.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function getDealIdsByLeadId(string $leadId): \Illuminate\Support\Collection
    {
        return DealModel::where('lead_id', $leadId)->pluck('id');
    }

    /**
     * Contar deals de un Lead.
     */
    public function countByLeadId(string $leadId): int
    {
        return DealModel::where('lead_id', $leadId)->count();
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
        return DealModel::where('sale_phase_id', $phaseId)->count();
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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 10)
    {
        $query = DealModel::with(['lead', 'salePhase']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('lead', function ($lq) use ($search) {
                        $lq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['phase_id'])) {
            $query->where('sale_phase_id', $filters['phase_id']);
        }

        if (! empty($filters['source_type'])) {
            $query->whereHas('lead', fn ($lq) => $lq->where('source_type', $filters['source_type']));
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Obtener deals por fase con filtros (para Kanban).
     *
     * @param  array<string>  $phaseIds
     * @param  string|null  $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByPhaseIds(array $phaseIds, ?string $search = null)
    {
        $query = DealModel::with('lead')
            ->whereIn('sale_phase_id', $phaseIds);

        if ($search) {
            $query->where(function ($sq) use ($search) {
                $sq->where('deals.name', 'like', "%{$search}%")
                    ->orWhereHas('lead', function ($lq) use ($search) {
                        $lq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderByDesc('updated_at')->get();
    }

    /**
     * Obtener estadísticas de deals.
     *
     * @param  array<string>|null  $openPhaseIds  IDs de fases abiertas (no cerradas)
     * @return array{total: int, open: int, total_value: float}
     */
    public function getStats(?array $openPhaseIds = null): array
    {
        $total = DealModel::count();

        if ($openPhaseIds === null || empty($openPhaseIds)) {
            return [
                'total' => $total,
                'open' => 0,
                'total_value' => 0,
            ];
        }

        $openDeals = DealModel::whereIn('sale_phase_id', $openPhaseIds)->count();
        $totalValue = DealModel::whereIn('sale_phase_id', $openPhaseIds)->sum('value') ?? 0;

        return [
            'total' => $total,
            'open' => $openDeals,
            'total_value' => (float) $totalValue,
        ];
    }

    /**
     * Obtener deal con relaciones cargadas para cambio de fase.
     */
    public function findWithRelations(string $dealId): ?DealModel
    {
        return DealModel::with(['salePhase', 'lead'])->find($dealId);
    }
}
