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
        Schema::create('comissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurisdicao_id')->constrained('jurisdicoes')->onDelete('cascade');
            $table->string('nome'); // ex: "Comissão de Ritualística"
            $table->text('descricao')->nullable();
            $table->boolean('ativa')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('jurisdicao_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comissoes');
    }
};
