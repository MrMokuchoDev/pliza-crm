<?php

declare(strict_types=1);

namespace App\Application\DealComment\Commands;

use App\Application\DealComment\DTOs\DealCommentData;

/**
 * Comando para crear un nuevo comentario de negocio.
 */
readonly class CreateDealCommentCommand
{
    public function __construct(
        public DealCommentData $data,
    ) {}
}
