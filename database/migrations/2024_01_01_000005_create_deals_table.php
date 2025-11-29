<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lead_id');
            $table->uuid('sale_phase_id');
            $table->string('name');
            $table->decimal('value', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->date('estimated_close_date')->nullable();
            $table->date('close_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('lead_id')
                ->references('id')
                ->on('leads')
                ->onDelete('cascade');

            $table->foreign('sale_phase_id')
                ->references('id')
                ->on('sale_phases')
                ->onDelete('restrict');

            $table->index('lead_id');
            $table->index('sale_phase_id');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
