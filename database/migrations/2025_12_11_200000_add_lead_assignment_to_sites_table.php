<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            // Usuario por defecto para asignar leads de este sitio
            // Si es null, se usa round robin
            $table->uuid('default_user_id')->nullable()->after('is_active');

            // Índice para el round robin - guarda el último usuario asignado
            // Se incrementa circularmente entre los usuarios activos
            $table->unsignedInteger('round_robin_index')->default(0)->after('default_user_id');

            // Foreign key
            $table->foreign('default_user_id')
                ->references('uuid')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropForeign(['default_user_id']);
            $table->dropColumn(['default_user_id', 'round_robin_index']);
        });
    }
};
