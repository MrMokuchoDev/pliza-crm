<?php

declare(strict_types=1);

namespace App\Application\Deal\Handlers;

use App\Application\Deal\Queries\GetDealsByPhaseQuery;
use App\Domain\CustomField\ValueObjects\SystemCustomFields;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use Illuminate\Database\Eloquent\Collection;

/**
 * Handler para obtener deals por fase (para Kanban).
 */
class GetDealsByPhaseHandler
{
    /**
     * @return Collection<int, DealModel>
     */
    public function handle(GetDealsByPhaseQuery $query): Collection
    {
        $builder = DealModel::with(['lead', 'assignedTo'])
            ->whereIn('sale_phase_id', $query->phaseIds);

        // Filtro por usuario asignado (para vendedores)
        if ($query->onlyOwn && $query->userUuid) {
            $builder->where('assigned_to', $query->userUuid);
        }

        if ($query->search) {
            $builder->where(function ($q) use ($query) {
                // Buscar por nombre del deal
                $q->searchInCustomFields($query->search, SystemCustomFields::getDealSearchableFields())
                    // O buscar en el lead asociado
                    ->orWhereHas('lead', function ($leadQuery) use ($query) {
                        $leadQuery->searchInCustomFields($query->search, SystemCustomFields::getLeadSearchableFields());
                    });
            });
        }

        return $builder->orderByDesc('updated_at')->get();
    }
}
