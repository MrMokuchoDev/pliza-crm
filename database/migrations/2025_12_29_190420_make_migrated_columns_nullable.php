<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('phone', 20)->nullable()->change();
            $table->text('message')->nullable()->change();
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->decimal('value', 15, 2)->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->date('estimated_close_date')->nullable()->change();
        });

        echo "✓ Columnas físicas ahora son NULLABLE\n";
        echo "  - leads: name, email, phone, message\n";
        echo "  - deals: name, value, description, estimated_close_date\n";
        echo "\nEstas columnas se eliminarán en una migración posterior\n";
        echo "después de verificar que todo funcione correctamente.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('phone', 20)->nullable(false)->change();
            $table->text('message')->nullable(false)->change();
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->decimal('value', 15, 2)->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->date('estimated_close_date')->nullable(false)->change();
        });

        echo "Rollback: columnas físicas restauradas a NOT NULL\n";
    }
};
