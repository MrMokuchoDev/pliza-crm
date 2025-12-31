<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Marca los custom fields críticos como "del sistema" (is_system = true).
     * Estos campos no se pueden eliminar ni desactivar porque son necesarios
     * para el funcionamiento del sistema (ej: valor del deal para sumar en kanban).
     */
    public function up(): void
    {
        // Campos del sistema para LEADS
        // Todos los campos iniciales de Lead son del sistema
        $leadSystemFields = [
            'cf_lead_1', // Nombre
            'cf_lead_2', // Email
            'cf_lead_3', // Teléfono
            'cf_lead_4', // Mensaje
        ];

        DB::table('custom_fields')
            ->whereIn('name', $leadSystemFields)
            ->where('entity_type', 'lead')
            ->update(['is_system' => true]);

        echo "✓ Marcados " . count($leadSystemFields) . " campos de Lead como del sistema\n";

        // Campos del sistema para DEALS
        // CRÍTICO: cf_deal_2 (value) se usa para sumar en kanban
        // cf_deal_1 (name) se usa como identificador principal
        $dealSystemFields = [
            'cf_deal_1', // Nombre del Negocio (obligatorio)
            'cf_deal_2', // Valor Estimado (usado en sumas de kanban)
            'cf_deal_3', // Descripción
            'cf_deal_4', // Fecha Estimada de Cierre
        ];

        DB::table('custom_fields')
            ->whereIn('name', $dealSystemFields)
            ->where('entity_type', 'deal')
            ->update(['is_system' => true]);

        echo "✓ Marcados " . count($dealSystemFields) . " campos de Deal como del sistema\n";
    }

    /**
     * Revertir: quitar marca de sistema
     */
    public function down(): void
    {
        $allSystemFields = [
            'cf_lead_1',
            'cf_lead_2',
            'cf_lead_3',
            'cf_lead_4',
            'cf_deal_1',
            'cf_deal_2',
            'cf_deal_3',
            'cf_deal_4',
        ];

        DB::table('custom_fields')
            ->whereIn('name', $allSystemFields)
            ->update(['is_system' => false]);

        echo "Marca de sistema removida\n";
    }
};
