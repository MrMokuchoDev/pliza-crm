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
            // El accessor del modelo ya decodifica JSON automáticamente
            $this->customFieldValuesCache[$value->custom_field_id] = $value->value;
        }
    }

    /**
     * Override getAttribute para leer de custom fields si la columna no existe.
     */
    public function getAttribute($key)
    {
        // 1. Verificar si es un campo mapeado a custom field (legacy: name, email, etc.)
        $mapping = $this->getCustomFieldMapping();
        if (isset($mapping[$key])) {
            return $this->getCustomFieldValue($mapping[$key]);
        }

        // 2. Verificar si es un nombre técnico de custom field (cf_lead_1, cf_deal_2, etc.)
        if (preg_match('/^cf_(lead|deal)_\d+$/', $key)) {
            return $this->getCustomFieldValue($key);
        }

        // 3. Si no es custom field, usar comportamiento normal de Eloquent
        return parent::getAttribute($key);
    }

    /**
     * Override setAttribute para escribir en custom fields si la columna no existe.
     */
    public function setAttribute($key, $value)
    {
        // 1. Verificar si es un campo que está mapeado a custom field (legacy: name, email, etc.)
        $mapping = $this->getCustomFieldMapping();
        if (isset($mapping[$key])) {
            $this->setCustomFieldValue($mapping[$key], $value);
            return $this;
        }

        // 2. Verificar si es un nombre técnico de custom field (cf_lead_1, cf_deal_2, etc.)
        if (preg_match('/^cf_(lead|deal)_\d+$/', $key)) {
            $this->setCustomFieldValue($key, $value);
            return $this;
        }

        // 3. Si no es custom field, usar comportamiento normal
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
                    'value' => $value, // El mutator del modelo se encarga de encodear arrays
                ]
            );
        }
    }

    /**
     * Asignar múltiples custom fields desde un array.
     * Útil para asignar todos los custom fields de un DTO de una vez.
     *
     * @param array $customFields Array asociativo ['cf_lead_1' => 'valor', 'cf_lead_2' => 'valor', ...]
     * @return static
     */
    public function setCustomFieldsFromArray(array $customFields): static
    {
        foreach ($customFields as $fieldName => $value) {
            $this->setAttribute($fieldName, $value);
        }

        return $this;
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
                    'value' => $value, // El mutator del modelo se encarga de encodear arrays
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
        // Antes de guardar: remover atributos de custom fields para evitar errores
        static::saving(function ($model) {
            // Remover propiedades mapeadas (compatibilidad legacy)
            $mapping = $model->getCustomFieldMapping();
            foreach (array_keys($mapping) as $attribute) {
                unset($model->attributes[$attribute]);
            }

            // Remover nombres técnicos de custom fields (cf_lead_1, cf_deal_1, etc.)
            foreach ($model->attributes as $key => $value) {
                if (preg_match('/^cf_(lead|deal)_\d+$/', $key)) {
                    unset($model->attributes[$key]);
                }
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
