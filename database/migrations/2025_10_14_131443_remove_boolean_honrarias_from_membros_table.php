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
            // Remove os campos boolean antigos
            $table->dropColumn(['membro_cruz', 'coracao_cores']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membros', function (Blueprint $table) {
            // Restaura os campos caso seja necessário rollback
            $table->boolean('membro_cruz')->default(false)->after('motivo_afastamento');
            $table->boolean('coracao_cores')->default(false)->after('membro_cruz');
        });
    }
};
