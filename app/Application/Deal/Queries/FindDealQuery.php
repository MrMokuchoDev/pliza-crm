<?php

declare(strict_types=1);

namespace App\Application\Deal\Queries;

/**
 * Query para buscar un Deal por ID.
 */
readonly class FindDealQuery
{
    public const WITH_LEAD = 'lead';

    public const WITH_SALE_PHASE = 'salePhase';

    public const WITH_COMMENTS = 'comments';

    public const WITH_ALL = 'all';

    public function __construct(
        public string $dealId,
        public ?string $withRelations = null,
    ) {}
}
