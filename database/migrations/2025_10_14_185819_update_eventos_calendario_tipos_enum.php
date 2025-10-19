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
        Schema::table('eventos_calendario', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        Schema::table('eventos_calendario', function (Blueprint $table) {
            $table->enum('tipo', [
                'reuniao_ordinaria',
                'reuniao_extraordinaria', 
                'assembleia_geral',
                'assembleia_extraordinaria',
                'sessao_magna',
                'iniciacao',
                'elevacao',
                'exaltacao'
            ])->after('descricao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos_calendario', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        Schema::table('eventos_calendario', function (Blueprint $table) {
            $table->enum('tipo', ['reuniao', 'iniciacao', 'instalacao', 'cerimonia_publica', 'filantropia', 'outros'])->after('descricao');
        });
    }
};
