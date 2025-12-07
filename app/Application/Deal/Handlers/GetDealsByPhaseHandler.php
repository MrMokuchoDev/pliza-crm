<?php

declare(strict_types=1);

namespace App\Application\Deal\Handlers;

use App\Application\Deal\Queries\GetDealsByPhaseQuery;
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
        $builder = DealModel::with('lead')
            ->whereIn('sale_phase_id', $query->phaseIds);

        if ($query->search) {
            $search = $query->search;
            $builder->where(function ($sq) use ($search) {
                $sq->where('deals.name', 'like', "%{$search}%")
                    ->orWhereHas('lead', function ($lq) use ($search) {
                        $lq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        return $builder->orderByDesc('updated_at')->get();
    }
}
