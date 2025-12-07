<?php

declare(strict_types=1);

namespace App\Application\DealComment\Commands;

use App\Application\DealComment\DTOs\DealCommentData;

/**
 * Comando para actualizar un comentario de negocio existente.
 */
readonly class UpdateDealCommentCommand
{
    public function __construct(
        public string $commentId,
        public DealCommentData $data,
    ) {}
}
