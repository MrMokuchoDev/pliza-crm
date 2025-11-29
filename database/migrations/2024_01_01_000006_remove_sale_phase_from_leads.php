<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['sale_phase_id']);
            $table->dropColumn('sale_phase_id');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->uuid('sale_phase_id')->nullable();

            $table->foreign('sale_phase_id')
                ->references('id')
                ->on('sale_phases')
                ->onDelete('restrict');
        });
    }
};
