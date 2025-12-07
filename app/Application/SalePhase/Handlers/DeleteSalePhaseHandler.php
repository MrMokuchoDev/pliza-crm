<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Handlers;

use App\Application\Deal\Services\DealService;
use App\Application\SalePhase\Commands\DeleteSalePhaseCommand;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Illuminate\Support\Facades\DB;

/**
 * Handler para eliminar una fase de venta.
 *
 * Valida que:
 * - No sea la única fase activa
 * - Si tiene negocios, se transfieran a otra fase
 * - Si era default, asigna otra fase como default
 */
class DeleteSalePhaseHandler
{
    public function __construct(
        private readonly DealService $dealService,
    ) {}

    /**
     * @return array{success: bool, error?: string, transferred_deals?: int}
     */
    public function handle(DeleteSalePhaseCommand $command): array
    {
        $phase = SalePhaseModel::find($command->phaseId);

        if (! $phase) {
            return ['success' => false, 'error' => 'La fase no existe'];
        }

        // Verificar que no sea la única fase activa
        if (! $phase->is_closed) {
            $activeCount = SalePhaseModel::where('is_closed', false)->count();
            if ($activeCount <= 1) {
                return ['success' => false, 'error' => 'No puedes eliminar la única fase activa'];
            }
        }

        // Contar negocios en esta fase
        $dealsCount = $this->dealService->countByPhaseId($command->phaseId);

        // Si hay negocios, validar fase destino
        if ($dealsCount > 0) {
            if (! $command->transferToPhaseId) {
                return [
                    'success' => false,
                    'error' => "Debes seleccionar una fase destino para transferir los {$dealsCount} negocio(s)",
                ];
            }

            if ($command->transferToPhaseId === $command->phaseId) {
                return [
                    'success' => false,
                    'error' => 'La fase destino no puede ser la misma que se está eliminando',
                ];
            }

            $targetPhase = SalePhaseModel::find($command->transferToPhaseId);
            if (! $targetPhase) {
                return ['success' => false, 'error' => 'La fase destino no existe'];
            }
        }

        $transferredDeals = 0;

        DB::transaction(function () use ($command, $phase, $dealsCount, &$transferredDeals) {
            // Transferir negocios si es necesario
            if ($dealsCount > 0 && $command->transferToPhaseId) {
                $transferredDeals = $this->dealService->transferToPhase(
                    $command->phaseId,
                    $command->transferToPhaseId
                );
            }

            // Si era default, asignar a otra fase
            if ($phase->is_default) {
                $newDefault = SalePhaseModel::where('id', '!=', $command->phaseId)
                    ->where('is_closed', false)
                    ->first();

                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }

            // Eliminar la fase
            $phase->delete();
        });

        return [
            'success' => true,
            'transferred_deals' => $transferredDeals,
        ];
    }
}
