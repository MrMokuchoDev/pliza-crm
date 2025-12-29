<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\ReorderCustomFieldsCommand;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use Ramsey\Uuid\Uuid;

final class ReorderCustomFieldsHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
    ) {}

    public function handle(ReorderCustomFieldsCommand $command): void
    {
        foreach ($command->order as $fieldId => $position) {
            $field = $this->fieldRepository->findById(Uuid::fromString($fieldId));

            if (!$field) {
                continue;
            }

            $field->updateOrder($position);
            $this->fieldRepository->save($field);
        }
    }
}
