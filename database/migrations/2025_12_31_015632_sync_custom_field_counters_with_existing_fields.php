<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener el número más alto para cada entity_type
        $entityTypes = ['lead', 'deal'];

        foreach ($entityTypes as $entityType) {
            // Buscar todos los campos de este tipo y extraer el número más alto
            $maxNumber = DB::table('custom_fields')
                ->where('entity_type', $entityType)
                ->get()
                ->map(function ($field) use ($entityType) {
                    // Extraer el número del nombre del campo (cf_lead_5 -> 5)
                    if (preg_match('/^cf_' . $entityType . '_(\d+)$/', $field->name, $matches)) {
                        return (int) $matches[1];
                    }
                    return 0;
                })
                ->max() ?? 0;

            // Actualizar o crear el contador con el valor máximo encontrado
            DB::table('custom_field_counters')->updateOrInsert(
                ['entity_type' => $entityType],
                ['counter' => $maxNumber]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario revertir, los contadores se quedan como están
    }
};
