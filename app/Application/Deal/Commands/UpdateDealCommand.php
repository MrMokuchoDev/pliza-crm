<?php

declare(strict_types=1);

namespace App\Application\Deal\Commands;

use App\Application\Deal\DTOs\DealData;

/**
 * Comando para actualizar un Deal existente.
 */
readonly class UpdateDealCommand
{
    public function __construct(
        public string $dealId,
        public DealData $data,
    ) {}
}
