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
        Schema::create('jurisdicoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // ex: "Jurisdição do Paraná"
            $table->string('sigla', 20); // ex: "IORG-PR"
            $table->string('email');
            $table->string('telefone');
            $table->text('endereco_completo');
            $table->boolean('ativa')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurisdicoes');
    }
};
