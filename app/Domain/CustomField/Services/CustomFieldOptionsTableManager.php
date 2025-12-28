<?php

declare(strict_types=1);

namespace App\Domain\CustomField\Services;

use App\Domain\CustomField\ValueObjects\FieldName;

interface CustomFieldOptionsTableManager
{
    /**
     * Crear tabla de opciones para un campo
     */
    public function createOptionsTable(FieldName $fieldName): void;

    /**
     * Eliminar tabla de opciones
     */
    public function dropOptionsTable(FieldName $fieldName): void;

    /**
     * Verificar si existe la tabla de opciones
     */
    public function optionsTableExists(FieldName $fieldName): bool;

    /**
     * Agregar opción a un campo
     */
    public function addOption(FieldName $fieldName, string $value, string $label, int $order): void;

    /**
     * Actualizar opción
     */
    public function updateOption(FieldName $fieldName, string $id, string $value, string $label, int $order): void;

    /**
     * Eliminar opción
     */
    public function deleteOption(FieldName $fieldName, string $id): void;

    /**
     * Obtener todas las opciones de un campo
     */
    public function getOptions(FieldName $fieldName): array;

    /**
     * Reordenar opciones
     */
    public function reorderOptions(FieldName $fieldName, array $orderMap): void;
}
