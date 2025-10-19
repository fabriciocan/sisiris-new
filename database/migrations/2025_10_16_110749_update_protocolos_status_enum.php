<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Atualizar o enum do campo status para incluir novos valores
        DB::statement("ALTER TABLE protocolos MODIFY COLUMN status ENUM(
            'rascunho',
            'pendente',
            'em_analise',
            'aprovado',
            'rejeitado',
            'concluido',
            'cancelado',
            'aguardando_pagamento'
        ) NOT NULL DEFAULT 'rascunho'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para os valores originais
        DB::statement("ALTER TABLE protocolos MODIFY COLUMN status ENUM(
            'rascunho',
            'pendente',
            'em_analise',
            'aprovado',
            'rejeitado',
            'concluido',
            'cancelado'
        )");
    }
};
