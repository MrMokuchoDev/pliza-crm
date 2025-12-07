<?php

declare(strict_types=1);

namespace App\Application\DealComment\Handlers;

use App\Application\DealComment\Queries\FindDealCommentQuery;
use App\Infrastructure\Persistence\Eloquent\DealCommentModel;

/**
 * Handler para buscar un comentario de negocio.
 */
class FindDealCommentHandler
{
    public function handle(FindDealCommentQuery $query): ?DealCommentModel
    {
        return DealCommentModel::find($query->commentId);
    }
}
