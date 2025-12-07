<?php

declare(strict_types=1);

namespace App\Application\Deal\Queries;

/**
 * Query para contar deals en una fase.
 */
readonly class CountDealsByPhaseQuery
{
    public function __construct(
        public string $phaseId,
    ) {}
}
