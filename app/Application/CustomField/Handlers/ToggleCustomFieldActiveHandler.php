<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\ToggleCustomFieldActiveCommand;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use Ramsey\Uuid\Uuid;

final class ToggleCustomFieldActiveHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
    ) {}

    public function handle(ToggleCustomFieldActiveCommand $command): void
    {
        $field = $this->fieldRepository->findById(Uuid::fromString($command->id));

        if (!$field) {
            throw new \DomainException("Custom field not found: {$command->id}");
        }

        if ($command->isActive) {
            $field->activate();
        } else {
            $field->deactivate();
        }

        $this->fieldRepository->save($field);
    }
}
