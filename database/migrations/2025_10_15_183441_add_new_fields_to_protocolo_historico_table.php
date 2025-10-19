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
        Schema::table('protocolo_historico', function (Blueprint $table) {
            $table->string('acao')->nullable()->after('user_id');
            $table->text('descricao')->nullable()->after('acao');
            $table->string('etapa_anterior')->nullable()->after('status_novo');
            $table->string('etapa_nova')->nullable()->after('etapa_anterior');
            $table->json('dados_anteriores')->nullable()->after('etapa_nova');
            $table->json('dados_novos')->nullable()->after('dados_anteriores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocolo_historico', function (Blueprint $table) {
            $table->dropColumn([
                'acao',
                'descricao',
                'etapa_anterior',
                'etapa_nova',
                'dados_anteriores',
                'dados_novos'
            ]);
        });
    }
};
