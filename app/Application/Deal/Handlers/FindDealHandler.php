<?php

declare(strict_types=1);

namespace App\Application\Deal\Handlers;

use App\Application\Deal\Queries\FindDealQuery;
use App\Infrastructure\Persistence\Eloquent\DealModel;

/**
 * Handler para buscar un Deal por ID.
 */
class FindDealHandler
{
    public function handle(FindDealQuery $query): ?DealModel
    {
        $builder = DealModel::query();

        if ($query->withRelations === FindDealQuery::WITH_ALL) {
            $builder->with(['lead', 'salePhase', 'comments']);
        } elseif ($query->withRelations === FindDealQuery::WITH_LEAD) {
            $builder->with('lead');
        } elseif ($query->withRelations === FindDealQuery::WITH_SALE_PHASE) {
            $builder->with('salePhase');
        } elseif ($query->withRelations === FindDealQuery::WITH_COMMENTS) {
            $builder->with('comments');
        }

        return $builder->find($query->dealId);
    }
}
