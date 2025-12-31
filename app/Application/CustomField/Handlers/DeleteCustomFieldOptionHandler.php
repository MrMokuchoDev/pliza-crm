<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\DeleteCustomFieldOptionCommand;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use App\Domain\CustomField\Services\CustomFieldOptionsTableManagerInterface;
use Ramsey\Uuid\Uuid;

final class DeleteCustomFieldOptionHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
        private readonly CustomFieldOptionsTableManagerInterface $optionsTableManager,
    ) {}

    public function handle(DeleteCustomFieldOptionCommand $command): void
    {
        $customFieldId = Uuid::fromString($command->customFieldId);
        $optionId = Uuid::fromString($command->id);

        $field = $this->fieldRepository->findById($customFieldId);

        if (!$field) {
            throw new \DomainException("Custom field not found: {$command->customFieldId}");
        }

        $this->optionsTableManager->deleteOption($field->name(), $optionId);
    }
}
