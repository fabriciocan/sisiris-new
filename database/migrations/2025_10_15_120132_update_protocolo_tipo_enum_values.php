<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Atualizar o enum do campo tipo_protocolo para incluir os novos tipos
        DB::statement("ALTER TABLE protocolos MODIFY COLUMN tipo_protocolo ENUM(
            'iniciacao',
            'transferencia', 
            'afastamento',
            'retorno',
            'maioridade',
            'desligamento',
            'premios_honrarias',
            'homenageados_ano',
            'coracao_cores',
            'grande_cruz_cores',
            'novos_cargos_assembleia',
            'novos_cargos_conselho'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para os valores originais
        DB::statement("ALTER TABLE protocolos MODIFY COLUMN tipo_protocolo ENUM(
            'iniciacao',
            'transferencia',
            'afastamento', 
            'retorno',
            'maioridade',
            'desligamento',
            'premios_honrarias'
        )");
    }
};
