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
        Schema::create('honrarias_membros', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('membro_id')->constrained('membros')->onDelete('cascade');
            $table->enum('tipo_honraria', ['coracao_cores', 'grande_cruz_cores']);
            $table->year('ano_recebimento'); // Ano que recebeu a honraria
            $table->text('observacoes')->nullable(); // Observações sobre a honraria
            $table->foreignUuid('atribuido_por')->nullable()->constrained('users')->onDelete('set null'); // Quem atribuiu
            $table->timestamps();

            // Indexes
            $table->index('membro_id');
            $table->index('tipo_honraria');
            $table->index('ano_recebimento');
            
            // Constraint: Um membro pode receber a mesma honraria apenas uma vez por ano
            $table->unique(['membro_id', 'tipo_honraria', 'ano_recebimento'], 'unique_honraria_ano');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('honrarias_membros');
    }
};
