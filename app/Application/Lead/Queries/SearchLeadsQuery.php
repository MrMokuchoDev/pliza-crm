<?php

declare(strict_types=1);

namespace App\Application\Lead\Queries;

/**
 * Query para buscar leads por término.
 */
readonly class SearchLeadsQuery
{
    public function __construct(
        public string $term,
        public int $limit = 10,
        public ?string $userUuid = null,
        public bool $onlyOwn = false,
    ) {}
}
