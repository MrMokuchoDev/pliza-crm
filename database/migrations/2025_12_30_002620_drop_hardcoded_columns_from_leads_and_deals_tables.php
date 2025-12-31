<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Eliminar columnas hardcodeadas que ahora son custom fields.
     */
    public function up(): void
    {
        // Eliminar columnas de Leads que ahora son custom fields
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'email',
                'phone',
                'message',
            ]);
        });

        // Eliminar columnas de Deals que ahora son custom fields
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'value',
                'description',
                'estimated_close_date',
            ]);
        });
    }

    /**
     * Restaurar las columnas en caso de rollback.
     */
    public function down(): void
    {
        // Restaurar columnas de Leads
        Schema::table('leads', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->text('message')->nullable()->after('phone');
        });

        // Restaurar columnas de Deals
        Schema::table('deals', function (Blueprint $table) {
            $table->string('name')->nullable()->after('lead_id');
            $table->decimal('value', 12, 2)->nullable()->after('name');
            $table->text('description')->nullable()->after('value');
            $table->date('estimated_close_date')->nullable()->after('description');
        });
    }
};
