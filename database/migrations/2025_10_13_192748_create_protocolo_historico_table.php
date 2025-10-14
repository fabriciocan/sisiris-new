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
        Schema::create('protocolo_historico', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('protocolo_id')->constrained('protocolos')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status_anterior');
            $table->string('status_novo');
            $table->text('comentario')->nullable();
            $table->timestamp('created_at');

            // Indexes
            $table->index('protocolo_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocolo_historico');
    }
};
