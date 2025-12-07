<?php

declare(strict_types=1);

namespace App\Application\DealComment\Handlers;

use App\Application\DealComment\Commands\UpdateDealCommentCommand;
use App\Infrastructure\Persistence\Eloquent\DealCommentModel;

/**
 * Handler para actualizar un comentario de negocio existente.
 */
class UpdateDealCommentHandler
{
    public function handle(UpdateDealCommentCommand $command): ?DealCommentModel
    {
        $comment = DealCommentModel::find($command->commentId);

        if (! $comment) {
            return null;
        }

        $comment->update($command->data->toArray());

        return $comment->fresh();
    }
}
