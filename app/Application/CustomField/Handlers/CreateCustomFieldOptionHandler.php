<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\CreateCustomFieldOptionCommand;
use App\Application\CustomField\DTOs\CustomFieldOptionData;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use App\Domain\CustomField\Services\CustomFieldOptionsTableManagerInterface;
use Ramsey\Uuid\Uuid;

final class CreateCustomFieldOptionHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
        private readonly CustomFieldOptionsTableManagerInterface $optionsTableManager,
    ) {}

    public function handle(CreateCustomFieldOptionCommand $command): CustomFieldOptionData
    {
        $customFieldId = Uuid::fromString($command->customFieldId);

        $field = $this->fieldRepository->findById($customFieldId);

        if (!$field) {
            throw new \DomainException("Custom field not found: {$command->customFieldId}");
        }

        if (!$field->type()->requiresOptions()) {
            throw new \DomainException("Custom field type does not support options: {$field->type()->value}");
        }

        $optionId = Uuid::uuid4();
        $order = $command->order ?? $this->optionsTableManager->getNextOrder($field->name());

        $this->optionsTableManager->addOption(
            fieldName: $field->name(),
            id: $optionId,
            label: $command->label,
            value: $command->value,
            order: $order,
        );

        return new CustomFieldOptionData(
            id: $optionId->toString(),
            customFieldId: $command->customFieldId,
            label: $command->label,
            value: $command->value,
            order: $order,
        );
    }
}
