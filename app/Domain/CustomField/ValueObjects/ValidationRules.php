<?php

declare(strict_types=1);

namespace App\Domain\CustomField\ValueObjects;

use InvalidArgumentException;

final class ValidationRules
{
    private function __construct(
        private readonly array $rules
    ) {
        $this->validate();
    }

    public static function fromArray(array $rules): self
    {
        // Si es un array asociativo, usarlo directamente
        if (array_keys($rules) !== range(0, count($rules) - 1)) {
            return new self($rules);
        }

        // Si es un array indexado, parsear las reglas estilo Laravel
        $parsed = [];
        foreach ($rules as $rule) {
            if (!is_string($rule)) {
                continue;
            }

            if (str_contains($rule, ':')) {
                [$key, $value] = explode(':', $rule, 2);
                $parsed[$key] = $value;
            }
        }

        return new self($parsed);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Validar que las reglas sean correctas
     */
    private function validate(): void
    {
        $allowedKeys = ['min', 'max', 'min_length', 'max_length', 'decimals', 'min_date', 'max_date', 'mime_types', 'max_size'];

        foreach (array_keys($this->rules) as $key) {
            if (!in_array($key, $allowedKeys)) {
                throw new InvalidArgumentException("Invalid validation rule key: {$key}");
            }
        }
    }

    /**
     * Obtener regla específica
     */
    public function get(string $key): mixed
    {
        return $this->rules[$key] ?? null;
    }

    /**
     * Verificar si tiene una regla
     */
    public function has(string $key): bool
    {
        return isset($this->rules[$key]);
    }

    /**
     * Combinar con reglas por defecto del tipo de campo
     */
    public function mergeWithDefaults(FieldType $fieldType): array
    {
        $defaults = $fieldType->getDefaultValidationRules();
        $custom = $this->toLaravelRules();

        return array_merge($defaults, $custom);
    }

    /**
     * Convertir a formato de reglas de Laravel
     */
    public function toLaravelRules(): array
    {
        $rules = [];

        if ($this->has('min')) {
            $rules[] = 'min:' . $this->get('min');
        }

        if ($this->has('max')) {
            $rules[] = 'max:' . $this->get('max');
        }

        if ($this->has('min_length')) {
            $rules[] = 'min:' . $this->get('min_length');
        }

        if ($this->has('max_length')) {
            $rules[] = 'max:' . $this->get('max_length');
        }

        if ($this->has('decimals')) {
            $rules[] = 'decimal:0,' . $this->get('decimals');
        }

        if ($this->has('min_date')) {
            $rules[] = 'after_or_equal:' . $this->get('min_date');
        }

        if ($this->has('max_date')) {
            $rules[] = 'before_or_equal:' . $this->get('max_date');
        }

        if ($this->has('mime_types')) {
            $rules[] = 'mimes:' . implode(',', $this->get('mime_types'));
        }

        if ($this->has('max_size')) {
            $rules[] = 'max:' . $this->get('max_size'); // KB
        }

        return $rules;
    }

    public function toArray(): array
    {
        return $this->rules;
    }

    public function toJson(): string
    {
        return json_encode($this->rules);
    }
}
