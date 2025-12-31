<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\DTOs\CustomFieldOptionData;
use App\Application\CustomField\Queries\GetCustomFieldOptionsQuery;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use App\Domain\CustomField\Services\CustomFieldOptionsTableManagerInterface;
use Ramsey\Uuid\Uuid;

final class GetCustomFieldOptionsHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
        private readonly CustomFieldOptionsTableManagerInterface $optionsTableManager,
    ) {}

    /**
     * @return CustomFieldOptionData[]
     */
    public function handle(GetCustomFieldOptionsQuery $query): array
    {
        $customFieldId = Uuid::fromString($query->customFieldId);

        $field = $this->fieldRepository->findById($customFieldId);

        if (!$field) {
            throw new \DomainException("Custom field not found: {$query->customFieldId}");
        }

        if (!$field->type()->requiresOptions()) {
            return [];
        }

        $options = $this->optionsTableManager->getOptions($field->name());

        return array_map(
            fn($option) => new CustomFieldOptionData(
                id: $option['id'],
                customFieldId: $query->customFieldId,
                label: $option['label'],
                value: $option['value'],
                order: $option['order'],
            ),
            $options
        );
    }
}
