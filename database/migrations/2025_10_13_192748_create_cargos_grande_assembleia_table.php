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
        Schema::create('cargos_grande_assembleia', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('membro_id')->constrained('membros')->onDelete('cascade');
            $table->foreignId('tipo_cargo_id')->constrained('tipos_cargos_assembleia')->onDelete('cascade');
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->foreignUuid('atribuido_por')->constrained('users')->onDelete('cascade');
            $table->text('observacoes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('membro_id');
            $table->index('tipo_cargo_id');
            $table->index('ativo');
            $table->index('data_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargos_grande_assembleia');
    }
};
