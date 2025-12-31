<?php

declare(strict_types=1);

namespace App\Domain\CustomField\Services;

use App\Domain\CustomField\Entities\CustomField;
use App\Domain\CustomField\ValueObjects\FieldType;

final class CustomFieldValueNormalizer
{
    /**
     * Normaliza un valor según el tipo de campo
     */
    public function normalize(CustomField $field, mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Para campos que permiten múltiples valores (multiselect, checkbox)
        if ($field->type()->allowsMultipleValues() && is_array($value)) {
            return json_encode($value);
        }

        // Para otros tipos, convertir a string
        return (string) $value;
    }

    /**
     * Desnormaliza un valor string a su tipo nativo
     */
    public function denormalize(CustomField $field, ?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        // Para campos que permiten múltiples valores, decodificar JSON
        if ($field->type()->allowsMultipleValues()) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        // Para otros tipos, retornar string
        return $value;
    }
}
