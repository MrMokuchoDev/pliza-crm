<?php

declare(strict_types=1);

namespace App\Application\Update\Commands;

class CheckForUpdatesCommand
{
    public function __construct(
        public readonly bool $forceRefresh = false,
    ) {}
}
