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
        Schema::table('membros', function (Blueprint $table) {
            $table->integer('numero_membro')->nullable()->unique()->after('id');
            $table->index('numero_membro');
        });

        // Atualizar os registros existentes com numeração sequencial
        $membros = DB::table('membros')->orderBy('created_at')->get();
        $numero = 1;
        foreach ($membros as $membro) {
            DB::table('membros')->where('id', $membro->id)->update(['numero_membro' => $numero]);
            $numero++;
        }

        // Tornar o campo obrigatório após popular os dados
        Schema::table('membros', function (Blueprint $table) {
            $table->integer('numero_membro')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membros', function (Blueprint $table) {
            $table->dropIndex(['numero_membro']);
            $table->dropColumn('numero_membro');
        });
    }
};
