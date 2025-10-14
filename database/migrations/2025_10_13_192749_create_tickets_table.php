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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_ticket')->unique(); // ex: "TKT-2025-001"
            $table->foreignId('assembleia_id')->nullable()->constrained('assembleias')->onDelete('set null');
            $table->foreignId('comissao_id')->nullable()->constrained('comissoes')->onDelete('set null');
            $table->foreignUuid('solicitante_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('responsavel_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('categoria', ['duvida', 'suporte_tecnico', 'financeiro', 'ritual', 'evento', 'administrativo', 'outros']);
            $table->string('assunto');
            $table->text('descricao');
            $table->enum('prioridade', ['baixa', 'normal', 'alta', 'urgente'])->default('normal');
            $table->enum('status', ['aberto', 'em_atendimento', 'aguardando_resposta', 'resolvido', 'fechado', 'cancelado']);
            $table->dateTime('data_abertura');
            $table->dateTime('data_primeira_resposta')->nullable();
            $table->dateTime('data_resolucao')->nullable();
            $table->dateTime('data_fechamento')->nullable();
            $table->integer('avaliacao')->nullable(); // 1-5
            $table->text('comentario_avaliacao')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique('numero_ticket');
            $table->index('assembleia_id');
            $table->index('comissao_id');
            $table->index('solicitante_id');
            $table->index('responsavel_id');
            $table->index('status');
            $table->index('categoria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
