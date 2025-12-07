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
}
