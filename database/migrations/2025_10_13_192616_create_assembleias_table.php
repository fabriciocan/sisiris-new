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
        Schema::create('assembleias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurisdicao_id')->constrained('jurisdicoes')->onDelete('cascade');
            $table->integer('numero'); // ex: Assembleia nº 5
            $table->string('nome'); // ex: "Luz da Esperança"
            $table->string('cidade');
            $table->string('estado', 2)->default('PR');
            $table->text('endereco_completo');
            $table->date('data_fundacao');
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->boolean('ativa')->default(true);
            $table->string('loja_patrocinadora')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('jurisdicao_id');
            $table->index('cidade');
            $table->index('ativa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assembleias');
    }
};
