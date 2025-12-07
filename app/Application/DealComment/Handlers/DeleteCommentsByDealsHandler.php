<?php

declare(strict_types=1);

namespace App\Application\DealComment\Handlers;

use App\Application\DealComment\Commands\DeleteCommentsByDealsCommand;
use App\Infrastructure\Persistence\Eloquent\DealCommentModel;

/**
 * Handler para eliminar comentarios de múltiples negocios.
 * Usado en eliminación en cascada de leads.
 */
class DeleteCommentsByDealsHandler
{
    /**
     * @return int Número de comentarios eliminados
     */
    public function handle(DeleteCommentsByDealsCommand $command): int
    {
        $dealIds = $command->dealIds;

        if (empty($dealIds) || (is_countable($dealIds) && count($dealIds) === 0)) {
            return 0;
        }

        return DealCommentModel::whereIn('deal_id', $dealIds)->delete();
    }
}
