<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Limpiar grupos existentes (primero eliminar campos asociados)
        DB::table('custom_fields')->delete();
        DB::table('custom_field_groups')->delete();

        // Grupos para Lead
        DB::table('custom_field_groups')->insert([
            [
                'id' => Str::uuid()->toString(),
                'entity_type' => 'lead',
                'name' => 'Información Básica',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'entity_type' => 'lead',
                'name' => 'Datos de Contacto',
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'entity_type' => 'lead',
                'name' => 'Información Adicional',
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Grupos para Deal
        DB::table('custom_field_groups')->insert([
            [
                'id' => Str::uuid()->toString(),
                'entity_type' => 'deal',
                'name' => 'Información del Negocio',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'entity_type' => 'deal',
                'name' => 'Detalles Comerciales',
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'entity_type' => 'deal',
                'name' => 'Información Adicional',
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('custom_field_groups')->whereIn('entity_type', ['lead', 'deal'])->delete();
    }
};
