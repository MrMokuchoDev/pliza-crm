<?php

declare(strict_types=1);

namespace App\Application\CustomField\Services;

use App\Application\CustomField\Commands\CreateCustomFieldCommand;
use App\Application\CustomField\Commands\CreateCustomFieldGroupCommand;
use App\Application\CustomField\Commands\CreateCustomFieldOptionCommand;
use App\Application\CustomField\Commands\DeleteCustomFieldCommand;
use App\Application\CustomField\Commands\DeleteCustomFieldGroupCommand;
use App\Application\CustomField\Commands\DeleteCustomFieldOptionCommand;
use App\Application\CustomField\Commands\ReorderCustomFieldGroupsCommand;
use App\Application\CustomField\Commands\ReorderCustomFieldsCommand;
use App\Application\CustomField\Commands\SetCustomFieldValueCommand;
use App\Application\CustomField\Commands\ToggleCustomFieldActiveCommand;
use App\Application\CustomField\Commands\UpdateCustomFieldCommand;
use App\Application\CustomField\Commands\UpdateCustomFieldGroupCommand;
use App\Application\CustomField\Commands\UpdateCustomFieldOptionCommand;
use App\Application\CustomField\DTOs\CustomFieldData;
use App\Application\CustomField\DTOs\CustomFieldGroupData;
use App\Application\CustomField\DTOs\CustomFieldOptionData;
use App\Application\CustomField\DTOs\CustomFieldValueData;
use App\Application\CustomField\Handlers\CreateCustomFieldGroupHandler;
use App\Application\CustomField\Handlers\CreateCustomFieldHandler;
use App\Application\CustomField\Handlers\CreateCustomFieldOptionHandler;
use App\Application\CustomField\Handlers\DeleteCustomFieldGroupHandler;
use App\Application\CustomField\Handlers\DeleteCustomFieldHandler;
use App\Application\CustomField\Handlers\DeleteCustomFieldOptionHandler;
use App\Application\CustomField\Handlers\GetAvailableEntityTypesHandler;
use App\Application\CustomField\Handlers\GetCustomFieldByIdHandler;
use App\Application\CustomField\Handlers\GetCustomFieldGroupByIdHandler;
use App\Application\CustomField\Handlers\GetCustomFieldGroupsHandler;
use App\Application\CustomField\Handlers\GetCustomFieldOptionsHandler;
use App\Application\CustomField\Handlers\GetCustomFieldsByEntityHandler;
use App\Application\CustomField\Handlers\GetCustomFieldValuesForEntityHandler;
use App\Application\CustomField\Handlers\ReorderCustomFieldGroupsHandler;
use App\Application\CustomField\Handlers\ReorderCustomFieldsHandler;
use App\Application\CustomField\Handlers\SetCustomFieldValueHandler;
use App\Application\CustomField\Handlers\ToggleCustomFieldActiveHandler;
use App\Application\CustomField\Handlers\UpdateCustomFieldGroupHandler;
use App\Application\CustomField\Handlers\UpdateCustomFieldHandler;
use App\Application\CustomField\Handlers\UpdateCustomFieldOptionHandler;
use App\Application\CustomField\Queries\GetAvailableEntityTypesQuery;
use App\Application\CustomField\Queries\GetCustomFieldByIdQuery;
use App\Application\CustomField\Queries\GetCustomFieldGroupByIdQuery;
use App\Application\CustomField\Queries\GetCustomFieldGroupsQuery;
use App\Application\CustomField\Queries\GetCustomFieldOptionsQuery;
use App\Application\CustomField\Queries\GetCustomFieldsByEntityQuery;
use App\Application\CustomField\Queries\GetCustomFieldValuesForEntityQuery;

final class CustomFieldService
{
    public function __construct(
        // Group Handlers
        private readonly CreateCustomFieldGroupHandler $createGroupHandler,
        private readonly UpdateCustomFieldGroupHandler $updateGroupHandler,
        private readonly DeleteCustomFieldGroupHandler $deleteGroupHandler,
        private readonly GetCustomFieldGroupsHandler $getGroupsHandler,
        private readonly GetCustomFieldGroupByIdHandler $getGroupByIdHandler,
        private readonly ReorderCustomFieldGroupsHandler $reorderGroupsHandler,
        // Field Handlers
        private readonly CreateCustomFieldHandler $createFieldHandler,
        private readonly UpdateCustomFieldHandler $updateFieldHandler,
        private readonly DeleteCustomFieldHandler $deleteFieldHandler,
        private readonly GetCustomFieldsByEntityHandler $getFieldsByEntityHandler,
        private readonly GetCustomFieldByIdHandler $getFieldByIdHandler,
        private readonly ReorderCustomFieldsHandler $reorderFieldsHandler,
        private readonly ToggleCustomFieldActiveHandler $toggleFieldActiveHandler,
        // Value Handlers
        private readonly SetCustomFieldValueHandler $setValueHandler,
        private readonly GetCustomFieldValuesForEntityHandler $getValuesForEntityHandler,
        // Option Handlers
        private readonly CreateCustomFieldOptionHandler $createOptionHandler,
        private readonly UpdateCustomFieldOptionHandler $updateOptionHandler,
        private readonly DeleteCustomFieldOptionHandler $deleteOptionHandler,
        private readonly GetCustomFieldOptionsHandler $getOptionsHandler,
        // Entity Type Handlers
        private readonly GetAvailableEntityTypesHandler $getAvailableEntityTypesHandler,
    ) {}

    // ========== GROUP METHODS ==========

    public function createGroup(string $entityType, string $name, ?int $order = null): CustomFieldGroupData
    {
        $command = new CreateCustomFieldGroupCommand($entityType, $name, $order);
        return $this->createGroupHandler->handle($command);
    }

    public function updateGroup(string $id, string $name, ?int $order = null): CustomFieldGroupData
    {
        $command = new UpdateCustomFieldGroupCommand($id, $name, $order);
        return $this->updateGroupHandler->handle($command);
    }

    public function deleteGroup(string $id, ?string $targetGroupId = null): void
    {
        $command = new DeleteCustomFieldGroupCommand($id, $targetGroupId);
        $this->deleteGroupHandler->handle($command);
    }

    /**
     * @return CustomFieldGroupData[]
     */
    public function getGroupsByEntity(string $entityType): array
    {
        $query = new GetCustomFieldGroupsQuery($entityType);
        return $this->getGroupsHandler->handle($query);
    }

    public function getGroupById(string $id): ?CustomFieldGroupData
    {
        $query = new GetCustomFieldGroupByIdQuery($id);
        return $this->getGroupByIdHandler->handle($query);
    }

    /**
     * Reordena los grupos según el array de IDs proporcionado.
     * @param array<string> $groupIds Array de IDs en el nuevo orden
     */
    public function reorderGroups(array $groupIds): void
    {
        $command = new ReorderCustomFieldGroupsCommand($groupIds);
        $this->reorderGroupsHandler->handle($command);
    }

    // ========== FIELD METHODS ==========

    public function createField(
        string $entityType,
        string $label,
        string $type,
        ?string $groupId = null,
        ?string $defaultValue = null,
        bool $isRequired = false,
        ?array $validationRules = null,
        ?int $order = null,
        array $options = [],
    ): CustomFieldData {
        $command = new CreateCustomFieldCommand(
            entityType: $entityType,
            label: $label,
            type: $type,
            groupId: $groupId,
            defaultValue: $defaultValue,
            isRequired: $isRequired,
            validationRules: $validationRules,
            order: $order,
            options: $options,
        );
        return $this->createFieldHandler->handle($command);
    }

    public function updateField(
        string $id,
        ?string $label = null,
        ?string $groupId = null,
        ?string $defaultValue = null,
        ?bool $isRequired = null,
        ?bool $isActive = null,
        ?array $validationRules = null,
        ?int $order = null,
        ?array $options = null,
    ): CustomFieldData {
        $command = new UpdateCustomFieldCommand(
            id: $id,
            label: $label,
            groupId: $groupId,
            defaultValue: $defaultValue,
            isRequired: $isRequired,
            isActive: $isActive,
            validationRules: $validationRules,
            order: $order,
            options: $options,
        );
        return $this->updateFieldHandler->handle($command);
    }

    public function deleteField(string $id): void
    {
        $command = new DeleteCustomFieldCommand($id);
        $this->deleteFieldHandler->handle($command);
    }

    /**
     * @return CustomFieldData[]
     */
    public function getFieldsByEntity(string $entityType, bool $activeOnly = true, ?string $groupId = null): array
    {
        $query = new GetCustomFieldsByEntityQuery($entityType, $activeOnly, $groupId);
        return $this->getFieldsByEntityHandler->handle($query);
    }

    public function getFieldById(string $id): ?CustomFieldData
    {
        $query = new GetCustomFieldByIdQuery($id);
        return $this->getFieldByIdHandler->handle($query);
    }

    /**
     * @param array<string, int> $order Array de [field_id => order_position]
     */
    public function reorderFields(array $order): void
    {
        $command = new ReorderCustomFieldsCommand($order);
        $this->reorderFieldsHandler->handle($command);
    }

    public function toggleFieldActive(string $id, bool $isActive): void
    {
        $command = new ToggleCustomFieldActiveCommand($id, $isActive);
        $this->toggleFieldActiveHandler->handle($command);
    }

    // ========== VALUE METHODS ==========

    public function setFieldValue(string $customFieldId, string $entityType, string $entityId, mixed $value): CustomFieldValueData
    {
        $command = new SetCustomFieldValueCommand($customFieldId, $entityType, $entityId, $value);
        return $this->setValueHandler->handle($command);
    }

    /**
     * @return CustomFieldValueData[]
     */
    public function getValuesForEntity(string $entityType, string $entityId): array
    {
        $query = new GetCustomFieldValuesForEntityQuery($entityType, $entityId);
        return $this->getValuesForEntityHandler->handle($query);
    }

    // ========== OPTION METHODS ==========

    public function createOption(string $customFieldId, string $label, string $value, ?int $order = null): CustomFieldOptionData
    {
        $command = new CreateCustomFieldOptionCommand($customFieldId, $label, $value, $order);
        return $this->createOptionHandler->handle($command);
    }

    public function updateOption(
        string $id,
        string $customFieldId,
        ?string $label = null,
        ?string $value = null,
        ?int $order = null
    ): CustomFieldOptionData {
        $command = new UpdateCustomFieldOptionCommand($id, $customFieldId, $label, $value, $order);
        return $this->updateOptionHandler->handle($command);
    }

    public function deleteOption(string $id, string $customFieldId): void
    {
        $command = new DeleteCustomFieldOptionCommand($id, $customFieldId);
        $this->deleteOptionHandler->handle($command);
    }

    /**
     * @return CustomFieldOptionData[]
     */
    public function getOptions(string $customFieldId): array
    {
        $query = new GetCustomFieldOptionsQuery($customFieldId);
        return $this->getOptionsHandler->handle($query);
    }

    // ========== ENTITY TYPE METHODS ==========

    /**
     * Obtiene todos los tipos de entidad disponibles.
     * @return array<array{value: string, label: string}>
     */
    public function getAvailableEntityTypes(): array
    {
        $query = new GetAvailableEntityTypesQuery();
        return $this->getAvailableEntityTypesHandler->handle($query);
    }
}
