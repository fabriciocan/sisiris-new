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
        Schema::table('protocolos', function (Blueprint $table) {
            // Renomear campo tipo para tipo_protocolo para maior clareza
            $table->renameColumn('tipo', 'tipo_protocolo');
            
            // Adicionar novos tipos de protocolo
            // Nota: Será necessário alterar o enum em uma migration separada após o rename
            
            // Campos do workflow
            $table->string('etapa_atual', 50)->nullable()->after('status');
            $table->date('data_cerimonia')->nullable()->after('data_conclusao');
            $table->decimal('valor_taxa', 10, 2)->nullable()->after('data_cerimonia');
            $table->string('comprovante_pagamento', 500)->nullable()->after('valor_taxa');
            $table->text('feedback_rejeicao')->nullable()->after('comprovante_pagamento');
            $table->foreignUuid('aprovado_por')->nullable()->after('feedback_rejeicao')->constrained('users')->onDelete('set null');
            $table->timestamp('data_aprovacao')->nullable()->after('aprovado_por');
            
            // Campos JSON para dados específicos do protocolo
            $table->json('dados_membros')->nullable()->after('dados_json'); // Membros afetados pelo protocolo
            $table->json('configuracao_etapas')->nullable()->after('dados_membros'); // Configuração do workflow
            
            // Indexes
            $table->index('etapa_atual');
            $table->index('aprovado_por');
            $table->index('data_cerimonia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocolos', function (Blueprint $table) {
            $table->renameColumn('tipo_protocolo', 'tipo');
            $table->dropForeign(['aprovado_por']);
            $table->dropColumn([
                'etapa_atual',
                'data_cerimonia',
                'valor_taxa',
                'comprovante_pagamento',
                'feedback_rejeicao',
                'aprovado_por',
                'data_aprovacao',
                'dados_membros',
                'configuracao_etapas'
            ]);
        });
    }
};
