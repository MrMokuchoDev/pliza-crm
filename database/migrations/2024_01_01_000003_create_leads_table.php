<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('message')->nullable();
            $table->string('source_type', 20)->default('manual');
            $table->uuid('source_site_id')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->uuid('sale_phase_id');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sale_phase_id')
                ->references('id')
                ->on('sale_phases')
                ->onDelete('restrict');

            $table->foreign('source_site_id')
                ->references('id')
                ->on('sites')
                ->onDelete('set null');

            $table->index('source_type');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index(['name', 'email', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
