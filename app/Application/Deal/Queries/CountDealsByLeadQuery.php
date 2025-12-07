<?php

declare(strict_types=1);

namespace App\Application\Deal\Queries;

/**
 * Query para contar deals de un Lead.
 */
readonly class CountDealsByLeadQuery
{
    public function __construct(
        public string $leadId,
    ) {}
}
