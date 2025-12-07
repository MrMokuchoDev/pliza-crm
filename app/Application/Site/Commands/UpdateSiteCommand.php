<?php

declare(strict_types=1);

namespace App\Application\Site\Commands;

use App\Application\Site\DTOs\SiteData;

/**
 * Comando para actualizar un sitio web existente.
 */
readonly class UpdateSiteCommand
{
    public function __construct(
        public string $siteId,
        public SiteData $data,
    ) {}
}
