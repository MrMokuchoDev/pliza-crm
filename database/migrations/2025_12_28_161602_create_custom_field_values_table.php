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
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('custom_field_id');
            $table->string('entity_type');
            $table->uuid('entity_id');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('custom_field_id')
                ->references('id')
                ->on('custom_fields')
                ->onDelete('cascade');

            $table->unique(['custom_field_id', 'entity_type', 'entity_id'], 'unique_custom_field_value');
            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
    }
};
