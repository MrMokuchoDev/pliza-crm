<?php

declare(strict_types=1);

namespace App\Domain\CustomField\Services;

use App\Domain\CustomField\Entities\CustomField;

final class CustomFieldValidationService
{
    /**
     * Validar valor contra las reglas del campo
     */
    public function validate(CustomField $field, mixed $value): bool
    {
        $rules = $field->getValidationRules();

        // Para campos multi-valor
        if ($field->type()->allowsMultipleValues()) {
            if (!is_array($value)) {
                return false;
            }

            // Validar cada valor individualmente
            foreach ($value as $item) {
                if (!$this->validateSingleValue($field, $item)) {
                    return false;
                }
            }

            return true;
        }

        return $this->validateSingleValue($field, $value);
    }

    /**
     * Validar un valor individual
     */
    private function validateSingleValue(CustomField $field, mixed $value): bool
    {
        // Null/empty handling
        if ($value === null || $value === '') {
            return !$field->isRequired();
        }

        // Type-specific validation
        return match ($field->type()->value) {
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
            'number' => is_numeric($value),
            'date' => $this->isValidDate($value),
            default => true, // text, textarea, tel, etc.
        };
    }

    /**
     * Validar formato de fecha
     */
    private function isValidDate(string $value): bool
    {
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value;
    }

    /**
     * Obtener reglas de validación en formato Laravel
     */
    public function getLaravelRules(CustomField $field): array
    {
        return $field->getValidationRules();
    }

    /**
     * Validar valor contra opciones permitidas (para select, radio, etc.)
     */
    public function validateAgainstOptions(CustomField $field, mixed $value, array $allowedOptions): bool
    {
        if (!$field->type()->requiresOptions()) {
            return true;
        }

        $allowedValues = array_column($allowedOptions, 'value');

        if ($field->type()->allowsMultipleValues()) {
            if (!is_array($value)) {
                return false;
            }

            foreach ($value as $item) {
                if (!in_array($item, $allowedValues)) {
                    return false;
                }
            }

            return true;
        }

        return in_array($value, $allowedValues);
    }
}
