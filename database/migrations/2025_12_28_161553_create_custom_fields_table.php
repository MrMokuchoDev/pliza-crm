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
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('entity_type')->index();
            $table->uuid('group_id')->nullable();
            $table->string('name')->unique(); // cf_lead_1, cf_deal_1
            $table->string('label');
            $table->string('type'); // text, textarea, email, tel, number, select, radio, multiselect, checkbox, date, url
            $table->text('default_value')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('validation_rules')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('group_id')
                ->references('id')
                ->on('custom_field_groups')
                ->onDelete('set null');

            $table->index(['entity_type', 'is_active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
