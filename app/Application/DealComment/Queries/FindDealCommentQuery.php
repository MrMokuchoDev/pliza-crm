<?php

declare(strict_types=1);

namespace App\Application\DealComment\Queries;

/**
 * Query para buscar un comentario por su ID.
 */
readonly class FindDealCommentQuery
{
    public function __construct(
        public string $commentId,
    ) {}
}
