<?php

declare(strict_types=1);

namespace App\Application\SalePhase\Handlers;

use App\Application\SalePhase\Commands\ReorderPhasesCommand;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Illuminate\Support\Facades\DB;

/**
 * Handler para reordenar las fases de venta.
 */
class ReorderPhasesHandler
{
    public function handle(ReorderPhasesCommand $command): bool
    {
        if (empty($command->orderedIds)) {
            return false;
        }

        DB::transaction(function () use ($command) {
            foreach ($command->orderedIds as $index => $id) {
                SalePhaseModel::where('id', $id)->update(['order' => $index + 1]);
            }
        });

        return true;
    }
}
