<?php

declare(strict_types=1);

namespace App\Domain\CustomField\Services;

use App\Domain\CustomField\ValueObjects\FieldName;
use Ramsey\Uuid\UuidInterface;

interface CustomFieldOptionsTableManagerInterface
{
    /**
     * Crea una tabla dinámica para almacenar opciones de un campo
     * Nombre de tabla: {field_name}_options (ej: cf_lead_1_options)
     */
    public function createTable(FieldName $fieldName): void;

    /**
     * Elimina la tabla de opciones de un campo
     */
    public function dropTable(FieldName $fieldName): void;

    /**
     * Agrega una opción a la tabla del campo
     */
    public function addOption(FieldName $fieldName, UuidInterface $id, string $label, string $value, int $order): void;

    /**
     * Actualiza una opción existente
     */
    public function updateOption(FieldName $fieldName, UuidInterface $id, string $label, string $value, int $order): void;

    /**
     * Elimina una opción
     */
    public function deleteOption(FieldName $fieldName, UuidInterface $id): void;

    /**
     * Obtiene todas las opciones de un campo ordenadas
     *
     * @return array Array de arrays con keys: id, label, value, order
     */
    public function getOptions(FieldName $fieldName): array;

    /**
     * Obtiene una opción específica por ID
     *
     * @return array|null Array con keys: id, label, value, order
     */
    public function getOption(FieldName $fieldName, UuidInterface $id): ?array;

    /**
     * Obtiene el siguiente número de orden disponible
     */
    public function getNextOrder(FieldName $fieldName): int;

    /**
     * Verifica si existe la tabla de opciones
     */
    public function tableExists(FieldName $fieldName): bool;
}
