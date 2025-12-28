<?php

declare(strict_types=1);

namespace App\Domain\CustomField\ValueObjects;

use InvalidArgumentException;

final class FieldName
{
    private function __construct(
        private readonly string $value
    ) {
        $this->validate();
    }

    public static function create(string $value): self
    {
        return new self($value);
    }

    /**
     * Generar nombre de campo con formato cf_{entity}_{number}
     */
    public static function generate(EntityType $entityType, int $number): self
    {
        $value = sprintf('cf_%s_%d', $entityType->value, $number);
        return new self($value);
    }

    /**
     * Validar formato del nombre
     */
    private function validate(): void
    {
        if (!preg_match('/^cf_[a-z]+_\d+$/', $this->value)) {
            throw new InvalidArgumentException(
                "Invalid field name format. Expected: cf_{entity}_{number}"
            );
        }
    }

    /**
     * Extraer el entity type del nombre del campo
     */
    public function getEntityType(): EntityType
    {
        preg_match('/^cf_([a-z]+)_\d+$/', $this->value, $matches);
        return EntityType::fromString($matches[1]);
    }

    /**
     * Extraer el número del campo
     */
    public function getNumber(): int
    {
        preg_match('/^cf_[a-z]+_(\d+)$/', $this->value, $matches);
        return (int) $matches[1];
    }

    /**
     * Generar nombre de tabla de opciones para este campo
     * Ej: cf_lead_1 -> cf_lead_1_options
     */
    public function getOptionsTableName(): string
    {
        return $this->value . '_options';
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(FieldName $other): bool
    {
        return $this->value === $other->value;
    }
}
