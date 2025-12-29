<?php

declare(strict_types=1);

namespace App\Domain\Shared\Traits;

use App\Infrastructure\Persistence\Eloquent\CustomFieldModel;
use App\Infrastructure\Persistence\Eloquent\CustomFieldValueModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * Trait para modelos que soportan custom fields.
 *
 * Este trait permite que los modelos lean/escriban custom fields
 * como si fueran propiedades nativas, manteniendo compatibilidad
 * con código existente mientras migra de columnas físicas a custom fields.
 */
trait HasCustomFields
{
    /**
     * Cache de custom field values cargados para esta instancia.
     */
    protected array $customFieldValuesCache = [];

    /**
     * Mapeo de nombres de columnas físicas a custom field names.
     * Debe ser definido en el modelo que usa este trait.
     */
    abstract public function getCustomFieldMapping(): array;

    /**
     * Tipo de entidad para custom fields.
     * Debe ser definido en el modelo que usa este trait.
     */
    abstract protected function getEntityType(): string;

    /**
     * Relación con custom field values.
     */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValueModel::class, 'entity_id')
            ->where('entity_type', $this->getEntityType());
    }

    /**
     * Cargar eager custom field values.
     */
    public function loadCustomFieldValues(): void
    {
        if (!property_exists($this, 'customFieldValuesCache')) {
            $this->customFieldValuesCache = [];
        }

        // Si ya están cargados via eager loading, usarlos
        $values = $this->relationLoaded('customFieldValues')
            ? $this->customFieldValues
            : $this->customFieldValues()->get();

        foreach ($values as $value) {
            $this->customFieldValuesCache[$value->custom_field_id] = $value->value;
        }
    }

    /**
     * Override getAttribute para leer de custom fields si la columna no existe.
     */
    public function getAttribute($key)
    {
        // PRIMERO: Verificar si es un campo mapeado a custom field
        $mapping = $this->getCustomFieldMapping();
        if (isset($mapping[$key])) {
            return $this->getCustomFieldValue($mapping[$key]);
        }

        // Si no es custom field, usar comportamiento normal de Eloquent
        return parent::getAttribute($key);
    }

    /**
     * Override setAttribute para escribir en custom fields si la columna no existe.
     */
    public function setAttribute($key, $value)
    {
        // Verificar si es un campo que está mapeado a custom field
        $mapping = $this->getCustomFieldMapping();
        if (isset($mapping[$key])) {
            $this->setCustomFieldValue($mapping[$key], $value);
            return $this;
        }

        // Si no es custom field, usar comportamiento normal
        return parent::setAttribute($key, $value);
    }

    /**
     * Obtener valor de un custom field por su name.
     */
    protected function getCustomFieldValue(string $fieldName): mixed
    {
        // Cargar cache si no está cargado
        if (empty($this->customFieldValuesCache) && $this->exists) {
            $this->loadCustomFieldValues();
        }

        // Buscar el custom field por name
        $field = $this->getCustomFieldByName($fieldName);
        if (!$field) {
            return null;
        }

        return $this->customFieldValuesCache[$field->id] ?? $field->default_value;
    }

    /**
     * Establecer valor de un custom field por su name.
     */
    protected function setCustomFieldValue(string $fieldName, mixed $value): void
    {
        $field = $this->getCustomFieldByName($fieldName);
        if (!$field) {
            return;
        }

        // Guardar en cache
        $this->customFieldValuesCache[$field->id] = $value;

        // Si el modelo ya existe, actualizar en BD
        if ($this->exists) {
            CustomFieldValueModel::updateOrCreate(
                [
                    'custom_field_id' => $field->id,
                    'entity_type' => $this->getEntityType(),
                    'entity_id' => $this->getKey(),
                ],
                [
                    'value' => $value,
                ]
            );
        }
    }

    /**
     * Guardar todos los custom field values pendientes.
     * Llamar después de guardar el modelo.
     */
    public function saveCustomFieldValues(): void
    {
        if (empty($this->customFieldValuesCache)) {
            return;
        }

        foreach ($this->customFieldValuesCache as $fieldId => $value) {
            CustomFieldValueModel::updateOrCreate(
                [
                    'custom_field_id' => $fieldId,
                    'entity_type' => $this->getEntityType(),
                    'entity_id' => $this->getKey(),
                ],
                [
                    'value' => $value,
                ]
            );
        }
    }

    /**
     * Obtener custom field por name (con cache).
     */
    protected function getCustomFieldByName(string $fieldName): ?CustomFieldModel
    {
        $cacheKey = "custom_field_{$this->getEntityType()}_{$fieldName}";

        return Cache::remember($cacheKey, 3600, function () use ($fieldName) {
            return CustomFieldModel::where('entity_type', $this->getEntityType())
                ->where('name', $fieldName)
                ->where('is_active', true)
                ->first();
        });
    }

    /**
     * Hook: Antes y después de guardar el modelo.
     */
    protected static function bootHasCustomFields(): void
    {
        // Antes de guardar: remover atributos mapeados para evitar errores en columnas físicas
        static::saving(function ($model) {
            $mapping = $model->getCustomFieldMapping();
            foreach (array_keys($mapping) as $attribute) {
                // Remover del array de attributes para que no se guarde en la tabla física
                unset($model->attributes[$attribute]);
            }
        });

        // Después de guardar: persistir custom fields
        static::saved(function ($model) {
            $model->saveCustomFieldValues();
        });

        // Al recuperar: cargar custom fields
        static::retrieved(function ($model) {
            $model->loadCustomFieldValues();
        });
    }
}
