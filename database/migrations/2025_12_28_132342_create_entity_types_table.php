<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_types', function (Blueprint $table) {
            $table->string('name')->primary(); // 'lead', 'deal', 'client', etc.
            $table->string('label'); // 'Lead', 'Negocio', 'Cliente', etc.
            $table->string('model_class'); // App\Models\Lead::class
            $table->boolean('allows_custom_fields')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_types');
    }
};
