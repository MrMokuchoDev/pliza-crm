<?php

declare(strict_types=1);

namespace App\Application\DealComment\Commands;

/**
 * Comando para eliminar un comentario de negocio.
 */
readonly class DeleteDealCommentCommand
{
    public function __construct(
        public string $commentId,
    ) {}
}
