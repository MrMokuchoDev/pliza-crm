<?php

declare(strict_types=1);

namespace App\Application\Update\Commands;

class CreateBackupCommand
{
    public function __construct(
        public readonly bool $includeDatabase = true,
        public readonly bool $includeUploads = true,
    ) {}
}
