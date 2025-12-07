<?php

declare(strict_types=1);

namespace App\Application\Deal\Commands;

/**
 * Comando para eliminar un Deal y sus comentarios.
 */
readonly class DeleteDealCommand
{
    public function __construct(
        public string $dealId,
    ) {}
}
