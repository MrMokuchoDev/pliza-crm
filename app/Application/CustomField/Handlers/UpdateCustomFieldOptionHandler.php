<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\UpdateCustomFieldOptionCommand;
use App\Application\CustomField\DTOs\CustomFieldOptionData;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use App\Domain\CustomField\Services\CustomFieldOptionsTableManagerInterface;
use Ramsey\Uuid\Uuid;

final class UpdateCustomFieldOptionHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
        private readonly CustomFieldOptionsTableManagerInterface $optionsTableManager,
    ) {}

    public function handle(UpdateCustomFieldOptionCommand $command): CustomFieldOptionData
    {
        $customFieldId = Uuid::fromString($command->customFieldId);
        $optionId = Uuid::fromString($command->id);

        $field = $this->fieldRepository->findById($customFieldId);

        if (!$field) {
            throw new \DomainException("Custom field not found: {$command->customFieldId}");
        }

        $option = $this->optionsTableManager->getOption($field->name(), $optionId);

        if (!$option) {
            throw new \DomainException("Option not found: {$command->id}");
        }

        $updatedData = [
            'label' => $command->label ?? $option['label'],
            'value' => $command->value ?? $option['value'],
            'order' => $command->order ?? $option['order'],
        ];

        $this->optionsTableManager->updateOption(
            fieldName: $field->name(),
            id: $optionId,
            label: $updatedData['label'],
            value: $updatedData['value'],
            order: $updatedData['order'],
        );

        return new CustomFieldOptionData(
            id: $command->id,
            customFieldId: $command->customFieldId,
            label: $updatedData['label'],
            value: $updatedData['value'],
            order: $updatedData['order'],
        );
    }
}
