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
        Schema::create('protocolo_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocolo_id')->constrained('protocolos')->onDelete('cascade');
            $table->string('nome_arquivo');
            $table->string('caminho_arquivo');
            $table->string('tipo_arquivo');
            $table->integer('tamanho'); // bytes
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index('protocolo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocolo_anexos');
    }
};
