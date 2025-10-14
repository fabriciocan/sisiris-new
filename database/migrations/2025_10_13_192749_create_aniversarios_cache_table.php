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
        Schema::create('aniversarios_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('membro_id')->constrained('membros')->onDelete('cascade');
            $table->foreignId('assembleia_id')->constrained('assembleias')->onDelete('cascade');
            $table->enum('tipo', ['membro', 'iniciacao', 'maioridade']);
            $table->integer('mes'); // 1-12
            $table->integer('dia'); // 1-31
            $table->timestamp('updated_at');

            // Indexes
            $table->index('membro_id');
            $table->index('assembleia_id');
            $table->index(['mes', 'dia']); // Composite index
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aniversarios_cache');
    }
};
