<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

return new class extends Migration
{
    /**
     * Convierte los campos hardcoded de leads y deals a custom fields.
     * Migra los datos existentes de las columnas físicas a custom_field_values.
     *
     * IMPORTANTE: Las columnas físicas NO se eliminan aquí por seguridad.
     * Ejecutar migración posterior para eliminarlas después de verificar.
     */
    public function up(): void
    {
        // Obtener los grupos existentes
        $leadBasicGroup = DB::table('custom_field_groups')
            ->where('entity_type', 'lead')
            ->where('name', 'Información Básica')
            ->first();

        $leadContactGroup = DB::table('custom_field_groups')
            ->where('entity_type', 'lead')
            ->where('name', 'Datos de Contacto')
            ->first();

        $dealInfoGroup = DB::table('custom_field_groups')
            ->where('entity_type', 'deal')
            ->where('name', 'Información del Negocio')
            ->first();

        $dealCommercialGroup = DB::table('custom_field_groups')
            ->where('entity_type', 'deal')
            ->where('name', 'Detalles Comerciales')
            ->first();

        if (!$leadBasicGroup || !$leadContactGroup || !$dealInfoGroup || !$dealCommercialGroup) {
            throw new \Exception('Grupos de custom fields no encontrados. Ejecuta la migración seed_default_custom_field_groups primero.');
        }

        // ========================================
        // PARTE 1: Crear custom fields para LEADS
        // ========================================

        $leadNameId = Uuid::uuid4()->toString();
        $leadEmailId = Uuid::uuid4()->toString();
        $leadPhoneId = Uuid::uuid4()->toString();
        $leadMessageId = Uuid::uuid4()->toString();

        $leadFields = [
            [
                'id' => $leadNameId,
                'name' => 'cf_lead_1',
                'entity_type' => 'lead',
                'group_id' => $leadBasicGroup->id,
                'type' => 'text',
                'label' => 'Nombre',
                'default_value' => null,
                'is_required' => false, // Al menos email o phone es requerido (validación en código)
                'is_active' => true,
                'validation_rules' => json_encode(['max_length' => 255]),
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $leadEmailId,
                'name' => 'cf_lead_2',
                'entity_type' => 'lead',
                'group_id' => $leadContactGroup->id,
                'type' => 'email',
                'label' => 'Email',
                'default_value' => null,
                'is_required' => false, // Al menos email o phone es requerido (validación en código)
                'is_active' => true,
                'validation_rules' => json_encode(['max_length' => 255]),
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $leadPhoneId,
                'name' => 'cf_lead_3',
                'entity_type' => 'lead',
                'group_id' => $leadContactGroup->id,
                'type' => 'text',
                'label' => 'Teléfono',
                'default_value' => null,
                'is_required' => false, // Al menos email o phone es requerido (validación en código)
                'is_active' => true,
                'validation_rules' => json_encode(['max_length' => 20]),
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $leadMessageId,
                'name' => 'cf_lead_4',
                'entity_type' => 'lead',
                'group_id' => $leadBasicGroup->id,
                'type' => 'textarea',
                'label' => 'Mensaje / Notas',
                'default_value' => null,
                'is_required' => false,
                'is_active' => true,
                'validation_rules' => json_encode(['max_length' => 5000]),
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('custom_fields')->insert($leadFields);
        echo "✓ Custom fields de Lead creados\n";

        // ========================================
        // PARTE 2: Crear custom fields para DEALS
        // ========================================

        $dealNameId = Uuid::uuid4()->toString();
        $dealValueId = Uuid::uuid4()->toString();
        $dealDescriptionId = Uuid::uuid4()->toString();
        $dealEstimatedCloseDateId = Uuid::uuid4()->toString();

        $dealFields = [
            [
                'id' => $dealNameId,
                'name' => 'cf_deal_1',
                'entity_type' => 'deal',
                'group_id' => $dealInfoGroup->id,
                'type' => 'text',
                'label' => 'Nombre del Negocio',
                'default_value' => null,
                'is_required' => true,
                'is_active' => true,
                'validation_rules' => json_encode(['max_length' => 255]),
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $dealValueId,
                'name' => 'cf_deal_2',
                'entity_type' => 'deal',
                'group_id' => $dealCommercialGroup->id,
                'type' => 'number',
                'label' => 'Valor Estimado',
                'default_value' => null,
                'is_required' => false,
                'is_active' => true,
                'validation_rules' => json_encode(['min' => 0]),
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $dealDescriptionId,
                'name' => 'cf_deal_3',
                'entity_type' => 'deal',
                'group_id' => $dealInfoGroup->id,
                'type' => 'textarea',
                'label' => 'Descripción',
                'default_value' => null,
                'is_required' => false,
                'is_active' => true,
                'validation_rules' => json_encode(['max_length' => 5000]),
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $dealEstimatedCloseDateId,
                'name' => 'cf_deal_4',
                'entity_type' => 'deal',
                'group_id' => $dealCommercialGroup->id,
                'type' => 'date',
                'label' => 'Fecha Estimada de Cierre',
                'default_value' => null,
                'is_required' => false,
                'is_active' => true,
                'validation_rules' => json_encode([]),
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('custom_fields')->insert($dealFields);
        echo "✓ Custom fields de Deal creados\n";

        // ========================================
        // PARTE 3: Migrar datos de LEADS
        // ========================================

        echo "Migrando datos de leads...\n";

        $leads = DB::table('leads')
            ->select('id', 'name', 'email', 'phone', 'message')
            ->get();

        $leadFieldsMap = [
            'name' => $leadNameId,
            'email' => $leadEmailId,
            'phone' => $leadPhoneId,
            'message' => $leadMessageId,
        ];

        $valuesToInsert = [];
        foreach ($leads as $lead) {
            foreach ($leadFieldsMap as $column => $fieldId) {
                $value = $lead->{$column};
                // Insertar SIEMPRE, incluso valores vacíos, para consistencia
                $valuesToInsert[] = [
                    'id' => Uuid::uuid4()->toString(),
                    'custom_field_id' => $fieldId,
                    'entity_type' => 'lead',
                    'entity_id' => $lead->id,
                    'value' => $value !== null ? (string) $value : '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($valuesToInsert)) {
            // Insertar en chunks para evitar problemas de memoria
            foreach (array_chunk($valuesToInsert, 500) as $chunk) {
                DB::table('custom_field_values')->insert($chunk);
            }
            echo "✓ Migrados " . count($valuesToInsert) . " valores de leads\n";
        } else {
            echo "✓ No hay valores de leads para migrar\n";
        }

        // ========================================
        // PARTE 4: Migrar datos de DEALS
        // ========================================

        echo "Migrando datos de deals...\n";

        $deals = DB::table('deals')
            ->select('id', 'name', 'value', 'description', 'estimated_close_date')
            ->get();

        $dealFieldsMap = [
            'name' => $dealNameId,
            'value' => $dealValueId,
            'description' => $dealDescriptionId,
            'estimated_close_date' => $dealEstimatedCloseDateId,
        ];

        $valuesToInsert = [];
        foreach ($deals as $deal) {
            foreach ($dealFieldsMap as $column => $fieldId) {
                $value = $deal->{$column};
                // Insertar SIEMPRE, incluso valores vacíos, para consistencia
                $valuesToInsert[] = [
                    'id' => Uuid::uuid4()->toString(),
                    'custom_field_id' => $fieldId,
                    'entity_type' => 'deal',
                    'entity_id' => $deal->id,
                    'value' => $value !== null ? (string) $value : '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($valuesToInsert)) {
            foreach (array_chunk($valuesToInsert, 500) as $chunk) {
                DB::table('custom_field_values')->insert($chunk);
            }
            echo "✓ Migrados " . count($valuesToInsert) . " valores de deals\n";
        } else {
            echo "✓ No hay valores de deals para migrar\n";
        }

        echo "\n==============================================\n";
        echo "✓ Migración completada exitosamente\n";
        echo "==============================================\n";
        echo "IMPORTANTE: Las columnas físicas (name, email, phone, etc.) \n";
        echo "se mantienen por seguridad. Verifica que todo funcione \n";
        echo "correctamente antes de ejecutar la siguiente migración \n";
        echo "que las eliminará definitivamente.\n";
        echo "==============================================\n";
    }

    /**
     * Revertir la migración (restaurar estado original)
     */
    public function down(): void
    {
        // Eliminar custom field values migrados
        $fieldNames = [
            'cf_lead_1',
            'cf_lead_2',
            'cf_lead_3',
            'cf_lead_4',
            'cf_deal_1',
            'cf_deal_2',
            'cf_deal_3',
            'cf_deal_4',
        ];

        $fieldIds = DB::table('custom_fields')
            ->whereIn('name', $fieldNames)
            ->pluck('id');

        DB::table('custom_field_values')
            ->whereIn('custom_field_id', $fieldIds)
            ->delete();

        // Eliminar custom fields
        DB::table('custom_fields')
            ->whereIn('name', $fieldNames)
            ->delete();

        echo "Rollback completado. Datos originales preservados en columnas físicas.\n";
    }
};
