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
        Schema::create('cargos_assembleia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembleia_id')->constrained('assembleias')->onDelete('cascade');
            $table->foreignUuid('membro_id')->nullable()->constrained('membros')->onDelete('set null');
            $table->foreignId('tipo_cargo_id')->constrained('tipos_cargos_assembleia')->onDelete('cascade');
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('assembleia_id');
            $table->index('membro_id');
            $table->index('tipo_cargo_id');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargos_assembleia');
    }
};
