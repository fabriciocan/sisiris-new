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
        Schema::create('protocolo_membros', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('protocolo_id')->constrained('protocolos')->onDelete('cascade');
            $table->foreignUuid('membro_id')->constrained('membros')->onDelete('cascade');
            $table->boolean('presente_cerimonia')->nullable(); // Para protocolos com cerimônia
            $table->text('observacoes')->nullable(); // Observações específicas do membro no protocolo
            $table->json('dados_especificos')->nullable(); // Dados específicos por tipo de protocolo
            $table->timestamps();

            // Indexes
            $table->index('protocolo_id');
            $table->index('membro_id');
            $table->index('presente_cerimonia');
            
            // Unique constraint para evitar duplicatas
            $table->unique(['protocolo_id', 'membro_id'], 'unique_protocolo_membro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocolo_membros');
    }
};
