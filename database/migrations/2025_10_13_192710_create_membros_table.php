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
        Schema::create('membros', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assembleia_id')->constrained('assembleias')->onDelete('cascade');
            $table->string('nome_completo');
            $table->date('data_nascimento');
            $table->string('cpf')->nullable()->unique();
            $table->string('telefone');
            $table->string('email')->nullable();
            $table->text('endereco_completo');
            $table->string('nome_mae');
            $table->string('telefone_mae');
            $table->string('nome_pai')->nullable();
            $table->string('telefone_pai')->nullable();
            $table->string('responsavel_legal')->nullable();
            $table->string('contato_responsavel');
            $table->date('data_iniciacao')->nullable();
            $table->string('madrinha');
            $table->date('data_maioridade')->nullable();
            $table->enum('status', ['candidata', 'ativa', 'afastada', 'maioridade', 'desligada']);
            $table->text('motivo_afastamento')->nullable();
            $table->boolean('membro_cruz')->default(false);
            $table->boolean('coracao_cores')->default(false);
            $table->date('homenageados_ano')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('assembleia_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('data_nascimento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membros');
    }
};
