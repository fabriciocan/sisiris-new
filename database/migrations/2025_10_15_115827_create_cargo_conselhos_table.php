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
        Schema::create('cargo_conselhos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('assembleia_id')->constrained('assembleias')->onDelete('cascade');
            $table->foreignUuid('membro_id')->constrained('membros')->onDelete('cascade');
            $table->enum('tipo_cargo', [
                'presidente',
                'preceptora_mae',
                'preceptora_mae_adjunta',
                'membro_conselho'
            ]);
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('concede_admin_acesso')->default(false); // Para cargos executivos
            $table->foreignUuid('protocolo_id')->nullable()->constrained('protocolos')->onDelete('set null');
            $table->foreignUuid('atribuido_por')->nullable()->constrained('users')->onDelete('set null');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('assembleia_id');
            $table->index('membro_id');
            $table->index('tipo_cargo');
            $table->index('ativo');
            $table->index('protocolo_id');
            
            // Unique constraint para garantir apenas 1 pessoa por cargo por assembleia
            $table->unique(['assembleia_id', 'tipo_cargo', 'ativo'], 'unique_active_cargo_per_assembleia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargo_conselhos');
    }
};
