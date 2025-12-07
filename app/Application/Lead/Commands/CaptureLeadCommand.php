<?php

declare(strict_types=1);

namespace App\Application\Lead\Commands;

use App\Domain\Lead\ValueObjects\SourceType;

/**
 * Command para capturar un Lead desde un widget externo.
 */
readonly class CaptureLeadCommand
{
    public function __construct(
        public string $siteId,
        public SourceType $sourceType,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $message = null,
        public ?string $sourceUrl = null,
        public ?string $userAgent = null,
        public ?string $ipAddress = null,
        public ?string $pageUrl = null,
    ) {}
}
