<?php

declare(strict_types=1);

namespace App\Application\Dashboard\Queries;

/**
 * Query para obtener el funnel de conversión por fases.
 */
readonly class GetConversionFunnelQuery
{
    public function __construct(
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {}
}
