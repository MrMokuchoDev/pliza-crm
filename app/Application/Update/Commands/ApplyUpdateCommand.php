<?php

declare(strict_types=1);

namespace App\Application\Update\Commands;

class ApplyUpdateCommand
{
    public function __construct(
        public readonly string $updateFilePath,
        public readonly string $targetVersion,
        public readonly ?string $backupPath = null,
    ) {}
}
