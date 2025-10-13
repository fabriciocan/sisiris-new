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
        Schema::create('protocolos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_protocolo')->unique(); // ex: "PR-2025-001"
            $table->foreignId('assembleia_id')->constrained('assembleias')->onDelete('cascade');
            $table->enum('tipo', ['iniciacao', 'transferencia', 'afastamento', 'retorno', 'maioridade', 'desligamento', 'premios_honrarias']);
            $table->string('titulo');
            $table->text('descricao');
            $table->foreignId('membro_id')->nullable()->constrained('membros')->onDelete('set null');
            $table->foreignId('solicitante_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['rascunho', 'pendente', 'em_analise', 'aprovado', 'rejeitado', 'concluido', 'cancelado']);
            $table->enum('prioridade', ['baixa', 'normal', 'alta', 'urgente'])->default('normal');
            $table->dateTime('data_solicitacao');
            $table->dateTime('data_conclusao')->nullable();
            $table->text('observacoes')->nullable();
            $table->json('dados_json')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique('numero_protocolo');
            $table->index('assembleia_id');
            $table->index('tipo');
            $table->index('status');
            $table->index('solicitante_id');
            $table->index('membro_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocolos');
    }
};
