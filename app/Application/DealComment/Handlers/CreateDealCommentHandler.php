<?php

declare(strict_types=1);

namespace App\Application\DealComment\Handlers;

use App\Application\DealComment\Commands\CreateDealCommentCommand;
use App\Infrastructure\Persistence\Eloquent\DealCommentModel;

/**
 * Handler para crear un nuevo comentario de negocio.
 */
class CreateDealCommentHandler
{
    public function handle(CreateDealCommentCommand $command): DealCommentModel
    {
        return DealCommentModel::create($command->data->toArray());
    }
}
