<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait para agregar capacidad de búsqueda en custom fields a modelos Eloquent.
 *
 * CAPA: Infraestructura
 * TIPO: Query Scope (Eloquent feature)
 *
 * Este trait proporciona scopes reutilizables que eliminan ~75 líneas de código
 * duplicado en handlers de búsqueda. Es una solución pragmática de infraestructura
 * mientras no se implemente el patrón Repository completo para Deal y Lead.
 *
 * NOTA ARQUITECTURA:
 * - Este trait es específico de Eloquent y vive en la capa de Infrastructure
 * - Los Handlers de Application lo usan indirectamente a través de los modelos
 * - En el futuro, esta lógica se moverá a Repositories (capa Domain)
 *
 * Uso desde Handlers:
 * ```php
 * // En GetPaginatedLeadsHandler
 * $builder = LeadModel::query()
 *     ->searchInCustomFields($search, SystemCustomFields::getLeadSearchableFields());
 * ```
 */
trait CustomFieldSearchable
{
    /**
     * Scope para buscar en múltiples custom fields usando LIKE.
     *
     * @param Builder $query
     * @param string $searchTerm Término a buscar
     * @param array<string> $fieldNames Nombres de los custom fields donde buscar (ej: ['cf_lead_1', 'cf_lead_2'])
     * @return Builder
     */
    public function scopeSearchInCustomFields(Builder $query, string $searchTerm, array $fieldNames): Builder
    {
        return $query->whereHas('customFieldValues', function ($cfQuery) use ($searchTerm, $fieldNames) {
            $cfQuery->where(function ($innerQuery) use ($searchTerm, $fieldNames) {
                foreach ($fieldNames as $fieldName) {
                    $innerQuery->orWhere(function ($fieldQuery) use ($searchTerm, $fieldName) {
                        $fieldQuery->whereHas('customField', function ($cfDefinition) use ($fieldName) {
                            $cfDefinition->where('name', $fieldName);
                        })
                        ->where('value', 'like', "%{$searchTerm}%");
                    });
                }
            });
        });
    }

    /**
     * Scope para buscar en custom fields de una relación (ej: lead asociado al deal).
     *
     * @param Builder $query
     * @param string $relationName Nombre de la relación (ej: 'lead')
     * @param string $searchTerm Término a buscar
     * @param array<string> $fieldNames Nombres de los custom fields donde buscar
     * @return Builder
     */
    public function scopeSearchInRelatedCustomFields(
        Builder $query,
        string $relationName,
        string $searchTerm,
        array $fieldNames
    ): Builder {
        return $query->whereHas($relationName, function ($relationQuery) use ($searchTerm, $fieldNames) {
            $relationQuery->searchInCustomFields($searchTerm, $fieldNames);
        });
    }

    /**
     * Scope para buscar valor exacto en un custom field específico.
     * Útil para búsquedas por email, ID, etc.
     *
     * @param Builder $query
     * @param string $fieldName Nombre del custom field (ej: 'cf_lead_2')
     * @param mixed $value Valor exacto a buscar
     * @return Builder
     */
    public function scopeWhereCustomField(Builder $query, string $fieldName, mixed $value): Builder
    {
        return $query->whereHas('customFieldValues', function ($cfQuery) use ($fieldName, $value) {
            $cfQuery->whereHas('customField', function ($cfDefinition) use ($fieldName) {
                $cfDefinition->where('name', $fieldName);
            })->where('value', $value);
        });
    }

    /**
     * Scope para buscar con LIKE en un custom field específico.
     * Útil para búsquedas parciales por teléfono, nombre, etc.
     *
     * @param Builder $query
     * @param string $fieldName Nombre del custom field (ej: 'cf_lead_3')
     * @param string $pattern Patrón para LIKE (ej: '%3356182%')
     * @return Builder
     */
    public function scopeWhereCustomFieldLike(Builder $query, string $fieldName, string $pattern): Builder
    {
        return $query->whereHas('customFieldValues', function ($cfQuery) use ($fieldName, $pattern) {
            $cfQuery->whereHas('customField', function ($cfDefinition) use ($fieldName) {
                $cfDefinition->where('name', $fieldName);
            })->where('value', 'like', $pattern);
        });
    }
}
