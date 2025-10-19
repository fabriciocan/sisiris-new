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
        Schema::create('tipos_cargos_assembleia', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // ex: "Ilustre Preceptora", "Fé", "Grande Ilustre Preceptora"
            $table->enum('categoria', ['administrativo', 'assembleia', 'grande_assembleia']);
            $table->boolean('is_admin')->default(false); // Define se tem acesso administrativo
            $table->integer('ordem')->default(0); // Para ordenação na exibição
            $table->boolean('ativo')->default(true);
            $table->enum('criado_por', ['sistema', 'jurisdicao'])->default('jurisdicao');
            $table->text('descricao')->nullable();
            $table->json('acessos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('categoria');
            $table->index('ativo');
            $table->index('ordem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_cargos_assembleia');
    }
};
