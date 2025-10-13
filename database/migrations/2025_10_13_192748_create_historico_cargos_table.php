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
        Schema::create('historico_cargos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membro_id')->constrained('membros')->onDelete('cascade');
            $table->foreignId('tipo_cargo_id')->constrained('tipos_cargos_assembleia')->onDelete('cascade');
            $table->foreignId('cargo_assembleia_id')->nullable()->constrained('cargos_assembleia')->onDelete('set null');
            $table->foreignId('cargo_grande_assembleia_id')->nullable()->constrained('cargos_grande_assembleia')->onDelete('set null');
            $table->foreignId('assembleia_id')->nullable()->constrained('assembleias')->onDelete('set null');
            $table->string('semestre'); // formato: 2025.1 ou 2025.2
            $table->enum('tipo_historico', ['assembleia', 'grande_assembleia']);
            $table->timestamp('created_at');

            // Indexes
            $table->index('membro_id');
            $table->index('tipo_cargo_id');
            $table->index('assembleia_id');
            $table->index('tipo_historico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historico_cargos');
    }
};
