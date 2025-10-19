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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tipo_usuario_id')->nullable()->after('cpf')->constrained('tipo_usuarios')->onDelete('set null');
            $table->enum('nivel_acesso', ['admin_assembleia', 'membro_jurisdicao', 'membro'])->default('membro')->after('tipo_usuario_id');
            
            // Indexes
            $table->index('tipo_usuario_id');
            $table->index('nivel_acesso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tipo_usuario_id']);
            $table->dropColumn(['tipo_usuario_id', 'nivel_acesso']);
        });
    }
};
