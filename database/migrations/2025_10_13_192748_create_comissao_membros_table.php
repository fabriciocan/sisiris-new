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
        Schema::create('comissao_membros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comissao_id')->constrained('comissoes')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('cargo', ['presidente', 'membro']);
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('comissao_id');
            $table->index('user_id');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comissao_membros');
    }
};
