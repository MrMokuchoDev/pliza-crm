<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_phases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->unsignedInteger('order')->default(0);
            $table->string('color', 7)->default('#6B7280');
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_won')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index('order');
            $table->index('is_closed');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_phases');
    }
};
