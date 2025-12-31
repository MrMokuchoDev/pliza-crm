<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Corrige el tipo del campo de teléfono de 'text' a 'tel'
     * para que el widget renderice correctamente el plugin intl-tel-input.
     */
    public function up(): void
    {
        DB::table('custom_fields')
            ->where('name', 'cf_lead_3')
            ->where('entity_type', 'lead')
            ->update(['type' => 'tel']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('custom_fields')
            ->where('name', 'cf_lead_3')
            ->where('entity_type', 'lead')
            ->update(['type' => 'text']);
    }
};
