<?php

declare(strict_types=1);

namespace App\Application\Site\Commands;

/**
 * Comando para regenerar la API key de un sitio.
 */
readonly class RegenerateApiKeyCommand
{
    public function __construct(
        public string $siteId,
    ) {}
}
