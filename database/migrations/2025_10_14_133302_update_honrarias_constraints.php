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
        Schema::table('honrarias_membros', function (Blueprint $table) {
            // Remove a constraint antiga que permitia múltiplas honrarias por ano
            $table->dropUnique('unique_honraria_ano');
            
            // Adiciona nova coluna para homenageados do ano
            $table->enum('tipo_honraria', ['coracao_cores', 'grande_cruz_cores', 'homenageados_ano'])->change();
        });
        
        // Como MySQL não suporta constraints condicionais, vou usar uma abordagem diferente
        // Criaremos a lógica de validação no modelo
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('honrarias_membros', function (Blueprint $table) {
            // Restaura enum original
            $table->enum('tipo_honraria', ['coracao_cores', 'grande_cruz_cores'])->change();
            
            // Restaura constraint antiga
            $table->unique(['membro_id', 'tipo_honraria', 'ano_recebimento'], 'unique_honraria_ano');
        });
    }
};
