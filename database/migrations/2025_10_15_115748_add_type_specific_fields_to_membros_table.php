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
        Schema::table('membros', function (Blueprint $table) {
            // Relacionamento com tipo de usuário
            $table->foreignId('tipo_usuario_id')->nullable()->after('assembleia_id')->constrained('tipo_usuarios')->onDelete('set null');
            
            // Campos específicos para Tio Maçom
            $table->string('loja_maconica', 200)->nullable()->after('madrinha');
            $table->enum('grau_maconico', ['aprendiz', 'companheiro', 'mestre'])->nullable()->after('loja_maconica');
            $table->date('data_companheiro')->nullable()->after('grau_maconico');
            $table->date('data_mestre')->nullable()->after('data_companheiro');
            
            // Campos específicos para Tia Estrela do Oriente
            $table->string('capitulo_estrela', 200)->nullable()->after('data_mestre');
            $table->date('data_iniciacao_arco_iris')->nullable()->after('capitulo_estrela');
            
            // Indexes
            $table->index('tipo_usuario_id');
            $table->index('grau_maconico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membros', function (Blueprint $table) {
            $table->dropForeign(['tipo_usuario_id']);
            $table->dropColumn([
                'tipo_usuario_id',
                'loja_maconica',
                'grau_maconico',
                'data_companheiro',
                'data_mestre',
                'capitulo_estrela',
                'data_iniciacao_arco_iris'
            ]);
        });
    }
};
