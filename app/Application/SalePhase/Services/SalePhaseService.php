<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Services;

use App\Application\Deal\Services\DealService;
use App\Application\SalePhase\Commands\CreateSalePhaseCommand;
use App\Application\SalePhase\Commands\DeleteSalePhaseCommand;
use App\Application\SalePhase\Commands\ReorderPhasesCommand;
use App\Application\SalePhase\Commands\SetDefaultPhaseCommand;
use App\Application\SalePhase\Commands\UpdateSalePhaseCommand;
use App\Application\SalePhase\DTOs\SalePhaseData;
use App\Application\SalePhase\Handlers\CreateSalePhaseHandler;
use App\Application\SalePhase\Handlers\DeleteSalePhaseHandler;
use App\Application\SalePhase\Handlers\ReorderPhasesHandler;
use App\Application\SalePhase\Handlers\SetDefaultPhaseHandler;
use App\Application\SalePhase\Handlers\UpdateSalePhaseHandler;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Illuminate\Support\Collection;

/**
 * Servicio de aplicación para gestionar Fases de Venta.
 * Orquesta los comandos y handlers.
 */
class SalePhaseService
{
    public function __construct(
        private readonly CreateSalePhaseHandler $createHandler,
        private readonly UpdateSalePhaseHandler $updateHandler,
        private readonly DeleteSalePhaseHandler $deleteHandler,
        private readonly ReorderPhasesHandler $reorderHandler,
        private readonly SetDefaultPhaseHandler $setDefaultHandler,
        private readonly DealService $dealService,
    ) {}

    /**
     * Crear una nueva fase de venta.
     */
    public function create(SalePhaseData $data): SalePhaseModel
    {
        $command = new CreateSalePhaseCommand($data);

        return $this->createHandler->handle($command);
    }

    /**
     * Actualizar una fase existente.
     */
    public function update(string $phaseId, SalePhaseData $data): ?SalePhaseModel
    {
        $command = new UpdateSalePhaseCommand($phaseId, $data);

        return $this->updateHandler->handle($command);
    }

    /**
     * Eliminar una fase con transferencia opcional de negocios.
     *
     * @return array{success: bool, error?: string, transferred_deals?: int}
     */
    public function delete(string $phaseId, ?string $transferToPhaseId = null): array
    {
        $command = new DeleteSalePhaseCommand($phaseId, $transferToPhaseId);

        return $this->deleteHandler->handle($command);
    }

    /**
     * Reordenar fases.
     *
     * @param  array<int, string>  $orderedIds
     */
    public function reorder(array $orderedIds): bool
    {
        $command = new ReorderPhasesCommand($orderedIds);

        return $this->reorderHandler->handle($command);
    }

    /**
     * Establecer una fase como la fase por defecto.
     */
    public function setAsDefault(string $phaseId): bool
    {
        $command = new SetDefaultPhaseCommand($phaseId);

        return $this->setDefaultHandler->handle($command);
    }

    /**
     * Buscar una fase por ID.
     */
    public function find(string $phaseId): ?SalePhaseModel
    {
        return SalePhaseModel::find($phaseId);
    }

    /**
     * Obtener todas las fases ordenadas.
     *
     * @return Collection<int, SalePhaseModel>
     */
    public function getAllOrdered(): Collection
    {
        return SalePhaseModel::orderBy('order')->get();
    }

    /**
     * Obtener fases activas (no cerradas) ordenadas.
     *
     * @return Collection<int, SalePhaseModel>
     */
    public function getActivePhases(): Collection
    {
        return SalePhaseModel::where('is_closed', false)
            ->orderBy('order')
            ->get();
    }

    /**
     * Obtener la fase por defecto.
     */
    public function getDefaultPhase(): ?SalePhaseModel
    {
        return SalePhaseModel::where('is_default', true)->first();
    }

    /**
     * Contar negocios en una fase (para mostrar en UI antes de eliminar).
     */
    public function getDealsCount(string $phaseId): int
    {
        return $this->dealService->countByPhaseId($phaseId);
    }

    /**
     * Contar fases activas (no cerradas).
     */
    public function countActivePhases(): int
    {
        return SalePhaseModel::where('is_closed', false)->count();
    }
}
