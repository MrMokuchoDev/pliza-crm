<?php

declare(strict_types=1);

namespace App\Application\Site\Commands;

/**
 * Comando para activar/desactivar un sitio web.
 */
readonly class ToggleSiteActiveCommand
{
    public function __construct(
        public string $siteId,
    ) {}
}
