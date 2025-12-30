<?php

declare(strict_types=1);

namespace App\Domain\CustomField\ValueObjects;

/**
 * Constantes centralizadas para los custom fields del sistema.
 *
 * Estos son los campos que anteriormente estaban hardcodeados en las tablas
 * y ahora son custom fields dinámicos marcados con is_system = true.
 */
final class SystemCustomFields
{
    // Lead fields
    public const LEAD_NAME = 'cf_lead_1';
    public const LEAD_EMAIL = 'cf_lead_2';
    public const LEAD_PHONE = 'cf_lead_3';
    public const LEAD_MESSAGE = 'cf_lead_4';

    // Deal fields
    public const DEAL_NAME = 'cf_deal_1';
    public const DEAL_VALUE = 'cf_deal_2';
    public const DEAL_DESCRIPTION = 'cf_deal_3';
    public const DEAL_ESTIMATED_CLOSE_DATE = 'cf_deal_4';

    /**
     * Obtener los campos de lead que son buscables.
     *
     * @return array<string>
     */
    public static function getLeadSearchableFields(): array
    {
        return [
            self::LEAD_NAME,
            self::LEAD_EMAIL,
            self::LEAD_PHONE,
        ];
    }

    /**
     * Obtener el campo de deal que es buscable (solo nombre).
     *
     * @return array<string>
     */
    public static function getDealSearchableFields(): array
    {
        return [
            self::DEAL_NAME,
        ];
    }

    /**
     * Verificar si un nombre de campo es un system field.
     */
    public static function isSystemField(string $fieldName): bool
    {
        return in_array($fieldName, [
            self::LEAD_NAME,
            self::LEAD_EMAIL,
            self::LEAD_PHONE,
            self::LEAD_MESSAGE,
            self::DEAL_NAME,
            self::DEAL_VALUE,
            self::DEAL_DESCRIPTION,
            self::DEAL_ESTIMATED_CLOSE_DATE,
        ]);
    }
}
