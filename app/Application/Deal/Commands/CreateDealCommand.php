<?php

declare(strict_types=1);

namespace App\Application\Deal\Commands;

use App\Application\Deal\DTOs\DealData;

/**
 * Comando para crear un nuevo Deal.
 */
readonly class CreateDealCommand
{
    public function __construct(
        public DealData $dealData,
        public ?array $leadData = null,
    ) {}
}
