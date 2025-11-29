<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('deal_id');
            $table->text('content');
            $table->enum('type', ['general', 'call', 'whatsapp', 'email'])->default('general');
            $table->timestamps();

            $table->foreign('deal_id')
                ->references('id')
                ->on('deals')
                ->onDelete('cascade');

            $table->index('created_at');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_comments');
    }
};
