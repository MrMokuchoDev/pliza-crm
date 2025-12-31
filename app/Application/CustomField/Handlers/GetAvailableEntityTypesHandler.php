<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Queries\GetAvailableEntityTypesQuery;
use App\Domain\CustomField\Repositories\EntityTypeRepositoryInterface;

final class GetAvailableEntityTypesHandler
{
    public function __construct(
        private readonly EntityTypeRepositoryInterface $entityTypeRepository,
    ) {}

    /**
     * @return array<array{value: string, label: string}>
     */
    public function handle(GetAvailableEntityTypesQuery $query): array
    {
        $entityTypes = $this->entityTypeRepository->getAllAvailable();

        return array_map(function (string $type) {
            return [
                'value' => $type,
                'label' => $this->entityTypeRepository->getLabel($type) ?? ucfirst($type),
            ];
        }, $entityTypes);
    }
}
