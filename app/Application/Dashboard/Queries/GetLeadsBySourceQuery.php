<?php

declare(strict_types=1);

namespace App\Application\Dashboard\Queries;

/**
 * Query para obtener leads agrupados por fuente (source_type).
 */
readonly class GetLeadsBySourceQuery
{
    public function __construct(
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {}
}
