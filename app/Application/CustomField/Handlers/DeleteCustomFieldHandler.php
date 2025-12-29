<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\DeleteCustomFieldCommand;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use App\Domain\CustomField\Repositories\CustomFieldValueRepositoryInterface;
use App\Domain\CustomField\Services\CustomFieldOptionsTableManagerInterface;
use Ramsey\Uuid\Uuid;

final class DeleteCustomFieldHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
        private readonly CustomFieldValueRepositoryInterface $valueRepository,
        private readonly CustomFieldOptionsTableManagerInterface $optionsTableManager,
    ) {}

    public function handle(DeleteCustomFieldCommand $command): void
    {
        $fieldId = Uuid::fromString($command->id);

        $field = $this->fieldRepository->findById($fieldId);

        if (!$field) {
            throw new \DomainException("Custom field not found: {$command->id}");
        }

        // Eliminar todos los valores asociados
        $this->valueRepository->deleteByField($fieldId);

        // Si el campo tiene tabla de opciones, eliminarla
        if ($field->type()->requiresOptions()) {
            $this->optionsTableManager->dropTable($field->name());
        }

        // Eliminar el campo
        $this->fieldRepository->delete($fieldId);
    }
}
