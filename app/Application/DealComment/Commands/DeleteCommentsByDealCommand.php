<?php

declare(strict_types=1);

namespace App\Application\DealComment\Commands;

/**
 * Comando para eliminar todos los comentarios de un negocio.
 */
readonly class DeleteCommentsByDealCommand
{
    public function __construct(
        public string $dealId,
    ) {}
}
