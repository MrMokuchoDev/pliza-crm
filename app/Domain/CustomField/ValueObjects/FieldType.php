<?php

declare(strict_types=1);

namespace App\Domain\CustomField\ValueObjects;

enum FieldType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case EMAIL = 'email';
    case TEL = 'tel';
    case NUMBER = 'number';
    case SELECT = 'select';
    case RADIO = 'radio';
    case MULTISELECT = 'multiselect';
    case CHECKBOX = 'checkbox';
    case DATE = 'date';
    case URL = 'url';

    /**
     * Tipos que requieren opciones
     */
    public function requiresOptions(): bool
    {
        return in_array($this, [
            self::SELECT,
            self::RADIO,
            self::MULTISELECT,
            self::CHECKBOX,
        ]);
    }

    /**
     * Tipos que permiten múltiples valores
     */
    public function allowsMultipleValues(): bool
    {
        return in_array($this, [
            self::MULTISELECT,
            self::CHECKBOX,
        ]);
    }

    /**
     * Obtener reglas de validación por defecto según el tipo
     */
    public function getDefaultValidationRules(): array
    {
        return match ($this) {
            self::TEXT => ['string', 'max:255'],
            self::TEXTAREA => ['string', 'max:5000'],
            self::EMAIL => ['email', 'max:255'],
            self::TEL => ['string', 'max:20'],
            self::NUMBER => ['numeric'],
            self::SELECT, self::RADIO => ['string'],
            self::MULTISELECT, self::CHECKBOX => ['array'],
            self::DATE => ['date'],
            self::URL => ['url', 'max:500'],
        };
    }

    /**
     * Obtener label amigable para UI
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::TEXT => 'Texto',
            self::TEXTAREA => 'Área de Texto',
            self::EMAIL => 'Email',
            self::TEL => 'Teléfono',
            self::NUMBER => 'Número',
            self::SELECT => 'Selector',
            self::RADIO => 'Radio Button',
            self::MULTISELECT => 'Selector Múltiple',
            self::CHECKBOX => 'Checkbox',
            self::DATE => 'Fecha',
            self::URL => 'URL',
        };
    }

    /**
     * Obtener todos los tipos disponibles
     */
    public static function all(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
