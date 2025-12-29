<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\UpdateCustomFieldCommand;
use App\Application\CustomField\DTOs\CustomFieldData;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use Ramsey\Uuid\Uuid;

final class UpdateCustomFieldHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
    ) {}

    public function handle(UpdateCustomFieldCommand $command): CustomFieldData
    {
        $field = $this->fieldRepository->findById(Uuid::fromString($command->id));

        if (!$field) {
            throw new \DomainException("Custom field not found: {$command->id}");
        }

        if ($command->label !== null) {
            $field->updateLabel($command->label);
        }

        if ($command->groupId !== null) {
            if ($command->groupId === '') {
                $field->removeGroup();
            } else {
                $field->updateGroup(Uuid::fromString($command->groupId));
            }
        }

        if ($command->defaultValue !== null) {
            $field->updateDefaultValue($command->defaultValue);
        }

        if ($command->isRequired !== null) {
            $field->updateRequired($command->isRequired);
        }

        if ($command->isActive !== null) {
            if ($command->isActive) {
                $field->activate();
            } else {
                $field->deactivate();
            }
        }

        if ($command->validationRules !== null) {
            $field->updateValidationRules($command->validationRules);
        }

        if ($command->order !== null) {
            $field->updateOrder($command->order);
        }

        $this->fieldRepository->save($field);

        return CustomFieldData::fromEntity($field);
    }
}
