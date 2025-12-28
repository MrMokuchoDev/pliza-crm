<?php

declare(strict_types=1);

namespace App\Domain\CustomField\Services;

use App\Domain\CustomField\ValueObjects\EntityType;
use App\Domain\CustomField\ValueObjects\FieldName;

interface CustomFieldNameGenerator
{
    /**
     * Generar el siguiente nombre de campo para una entidad
     */
    public function generateNext(EntityType $entityType): FieldName;

    /**
     * Obtener el próximo número disponible
     */
    public function getNextNumber(EntityType $entityType): int;
}
