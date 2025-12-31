<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('entity_types')->insert([
            [
                'name' => 'lead',
                'label' => 'Lead',
                'model_class' => 'App\\Models\\Lead',
                'allows_custom_fields' => true,
                'is_active' => true,
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'deal',
                'label' => 'Negocio',
                'model_class' => 'App\\Models\\Deal',
                'allows_custom_fields' => true,
                'is_active' => true,
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('entity_types')->whereIn('name', ['lead', 'deal'])->delete();
    }
};
