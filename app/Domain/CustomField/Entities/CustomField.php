<?php

declare(strict_types=1);

namespace App\Domain\CustomField\Entities;

use App\Domain\CustomField\ValueObjects\EntityType;
use App\Domain\CustomField\ValueObjects\FieldName;
use App\Domain\CustomField\ValueObjects\FieldType;
use App\Domain\CustomField\ValueObjects\ValidationRules;
use Ramsey\Uuid\UuidInterface;

final class CustomField
{
    public function __construct(
        private readonly UuidInterface $id,
        private EntityType $entityType,
        private ?UuidInterface $groupId,
        private FieldName $name,
        private string $label,
        private FieldType $type,
        private ?string $defaultValue,
        private bool $isRequired,
        private ValidationRules $validationRules,
        private int $order,
        private bool $isActive,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        UuidInterface $id,
        EntityType $entityType,
        ?UuidInterface $groupId,
        FieldName $name,
        string $label,
        FieldType $type,
        ?string $defaultValue,
        bool $isRequired,
        ValidationRules $validationRules,
        int $order
    ): self {
        return new self(
            id: $id,
            entityType: $entityType,
            groupId: $groupId,
            name: $name,
            label: $label,
            type: $type,
            defaultValue: $defaultValue,
            isRequired: $isRequired,
            validationRules: $validationRules,
            order: $order,
            isActive: true,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    // Getters
    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function entityType(): EntityType
    {
        return $this->entityType;
    }

    public function groupId(): ?UuidInterface
    {
        return $this->groupId;
    }

    public function name(): FieldName
    {
        return $this->name;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function type(): FieldType
    {
        return $this->type;
    }

    public function defaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function validationRules(): ValidationRules
    {
        return $this->validationRules;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Business methods
    public function update(
        string $label,
        ?UuidInterface $groupId,
        ?string $defaultValue,
        bool $isRequired,
        ValidationRules $validationRules,
        int $order
    ): void {
        $this->label = $label;
        $this->groupId = $groupId;
        $this->defaultValue = $defaultValue;
        $this->isRequired = $isRequired;
        $this->validationRules = $validationRules;
        $this->order = $order;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function changeOrder(int $order): void
    {
        $this->order = $order;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Verificar si el tipo de campo requiere tabla de opciones
     */
    public function requiresOptionsTable(): bool
    {
        return $this->type->requiresOptions();
    }

    /**
     * Obtener nombre de tabla de opciones
     */
    public function getOptionsTableName(): string
    {
        return $this->name->getOptionsTableName();
    }

    /**
     * Obtener reglas de validación completas
     */
    public function getValidationRules(): array
    {
        $rules = $this->validationRules->mergeWithDefaults($this->type);

        if ($this->isRequired) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }
}
