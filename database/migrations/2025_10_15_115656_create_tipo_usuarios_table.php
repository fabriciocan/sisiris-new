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
        Schema::create('tipo_usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique(); // menina_ativa, maioridade, tio_macom, etc.
            $table->string('nome', 100); // Nome amigável do tipo
            $table->text('descricao')->nullable(); // Descrição do tipo de usuário
            $table->boolean('requer_assembleia')->default(true); // Se requer assembleia
            $table->boolean('requer_pais_responsaveis')->default(true); // Se requer dados dos pais
            $table->json('campos_especificos')->nullable(); // Campos específicos por tipo
            $table->boolean('ativo')->default(true); // Se o tipo está ativo
            $table->timestamps();

            // Indexes
            $table->index('codigo');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_usuarios');
    }
};
