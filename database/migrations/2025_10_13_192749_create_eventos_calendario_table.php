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
        Schema::create('eventos_calendario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembleia_id')->nullable()->constrained('assembleias')->onDelete('cascade'); // Null = evento da jurisdição
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->enum('tipo', ['reuniao', 'iniciacao', 'instalacao', 'cerimonia_publica', 'filantropia', 'outros']);
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim')->nullable();
            $table->string('local')->nullable();
            $table->text('endereco')->nullable();
            $table->boolean('publico')->default(false); // Visível para meninas ativas
            $table->foreignId('criado_por')->constrained('users')->onDelete('cascade');
            $table->string('cor_evento')->nullable(); // Hex color para calendário
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('assembleia_id');
            $table->index('tipo');
            $table->index('data_inicio');
            $table->index('publico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos_calendario');
    }
};
