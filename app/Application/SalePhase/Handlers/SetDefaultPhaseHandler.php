<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Handlers;

use App\Application\SalePhase\Commands\SetDefaultPhaseCommand;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Illuminate\Support\Facades\DB;

/**
 * Handler para establecer una fase como la fase por defecto.
 */
class SetDefaultPhaseHandler
{
    public function handle(SetDefaultPhaseCommand $command): bool
    {
        $phase = SalePhaseModel::find($command->phaseId);

        if (! $phase) {
            return false;
        }

        // No permitir establecer una fase cerrada como default
        if ($phase->is_closed) {
            return false;
        }

        DB::transaction(function () use ($command) {
            // Quitar default de todas las fases
            SalePhaseModel::where('is_default', true)->update(['is_default' => false]);

            // Establecer la nueva fase como default
            SalePhaseModel::where('id', $command->phaseId)->update(['is_default' => true]);
        });

        return true;
    }
}
