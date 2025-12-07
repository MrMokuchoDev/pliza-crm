<?php

declare(strict_types=1);

namespace App\Application\Site\Commands;

/**
 * Comando para eliminar un sitio web.
 *
 * La eliminación fallará si el sitio tiene leads asociados.
 */
readonly class DeleteSiteCommand
{
    public function __construct(
        public string $siteId,
    ) {}
}
