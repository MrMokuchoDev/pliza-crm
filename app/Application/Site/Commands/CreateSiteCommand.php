<?php

declare(strict_types=1);

namespace App\Application\Site\Commands;

use App\Application\Site\DTOs\SiteData;

/**
 * Comando para crear un nuevo sitio web.
 */
readonly class CreateSiteCommand
{
    public function __construct(
        public SiteData $data,
    ) {}
}
