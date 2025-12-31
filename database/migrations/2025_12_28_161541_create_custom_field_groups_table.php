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
        Schema::create('custom_field_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('entity_type')->index();
            $table->string('name');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['entity_type', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_groups');
    }
};
