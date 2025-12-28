<?php

declare(strict_types=1);

namespace App\Domain\CustomField\ValueObjects;

enum EntityType: string
{
    case LEAD = 'lead';
    case DEAL = 'deal';

    /**
     * Obtener label amigable
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::LEAD => 'Lead',
            self::DEAL => 'Negocio',
        };
    }

    /**
     * Obtener el nombre de la clase del modelo
     */
    public function getModelClass(): string
    {
        return match ($this) {
            self::LEAD => \App\Infrastructure\Persistence\Eloquent\LeadModel::class,
            self::DEAL => \App\Infrastructure\Persistence\Eloquent\DealModel::class,
        };
    }

    /**
     * Obtener todos los tipos disponibles
     */
    public static function all(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Crear desde string
     */
    public static function fromString(string $value): self
    {
        return self::from($value);
    }
}
