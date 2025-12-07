<?php

declare(strict_types=1);

namespace App\Application\Lead\Queries;

/**
 * Query para buscar un Lead por email o teléfono (detección de duplicados).
 */
readonly class FindLeadByContactQuery
{
    public function __construct(
        public ?string $email = null,
        public ?string $phone = null,
        public bool $lockForUpdate = false,
    ) {}
}
