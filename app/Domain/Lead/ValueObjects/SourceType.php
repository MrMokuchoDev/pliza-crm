<?php

declare(strict_types=1);

namespace App\Domain\Lead\ValueObjects;

/**
 * Value Object para el tipo de origen del lead.
 */
enum SourceType: string
{
    case WHATSAPP_BUTTON = 'whatsapp_button';
    case PHONE_BUTTON = 'phone_button';
    case CONTACT_FORM = 'contact_form';
    case MANUAL = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::WHATSAPP_BUTTON => 'Botón WhatsApp',
            self::PHONE_BUTTON => 'Botón Llamada',
            self::CONTACT_FORM => 'Formulario Contacto',
            self::MANUAL => 'Manual',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::WHATSAPP_BUTTON => 'whatsapp',
            self::PHONE_BUTTON => 'phone',
            self::CONTACT_FORM => 'envelope',
            self::MANUAL => 'user',
        };
    }
}
