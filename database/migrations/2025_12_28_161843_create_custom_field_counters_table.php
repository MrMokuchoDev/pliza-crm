<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_field_counters', function (Blueprint $table) {
            $table->string('entity_type')->primary();
            $table->unsignedInteger('counter')->default(0);
            $table->timestamps();
        });

        // Inicializar contadores para entidades existentes
        DB::table('custom_field_counters')->insert([
            ['entity_type' => 'lead', 'counter' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['entity_type' => 'deal', 'counter' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_counters');
    }
};
