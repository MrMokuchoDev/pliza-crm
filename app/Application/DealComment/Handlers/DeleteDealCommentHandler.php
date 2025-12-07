<?php

declare(strict_types=1);

namespace App\Application\DealComment\Handlers;

use App\Application\DealComment\Commands\DeleteDealCommentCommand;
use App\Infrastructure\Persistence\Eloquent\DealCommentModel;

/**
 * Handler para eliminar un comentario de negocio.
 */
class DeleteDealCommentHandler
{
    public function handle(DeleteDealCommentCommand $command): bool
    {
        $comment = DealCommentModel::find($command->commentId);

        if (! $comment) {
            return false;
        }

        return (bool) $comment->delete();
    }
}
