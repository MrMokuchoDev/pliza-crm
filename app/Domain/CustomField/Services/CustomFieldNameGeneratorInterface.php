<?php

declare(strict_types=1);

namespace App\Domain\CustomField\Services;

use App\Domain\CustomField\ValueObjects\EntityType;
use App\Domain\CustomField\ValueObjects\FieldName;

interface CustomFieldNameGeneratorInterface
{
    /**
     * Genera el siguiente nombre de campo incremental para el tipo de entidad
     * Ejemplo: cf_lead_1, cf_lead_2, cf_deal_1, etc.
     */
    public function generateNext(EntityType $entityType): FieldName;

    /**
     * Obtiene el contador actual para un tipo de entidad
     */
    public function getCurrentCounter(EntityType $entityType): int;
}
