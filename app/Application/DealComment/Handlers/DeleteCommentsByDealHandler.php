<?php

declare(strict_types=1);

namespace App\Application\DealComment\Handlers;

use App\Application\DealComment\Commands\DeleteCommentsByDealCommand;
use App\Infrastructure\Persistence\Eloquent\DealCommentModel;

/**
 * Handler para eliminar todos los comentarios de un negocio.
 */
class DeleteCommentsByDealHandler
{
    /**
     * @return int Número de comentarios eliminados
     */
    public function handle(DeleteCommentsByDealCommand $command): int
    {
        return DealCommentModel::where('deal_id', $command->dealId)->delete();
    }
}
