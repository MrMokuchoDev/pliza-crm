<?php

declare(strict_types=1);

namespace App\Application\Update\Commands;

class DownloadUpdateCommand
{
    public function __construct(
        public readonly string $version,
        public readonly string $downloadUrl,
    ) {}
}
