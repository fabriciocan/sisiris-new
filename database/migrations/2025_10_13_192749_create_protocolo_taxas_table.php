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
        Schema::create('protocolo_taxas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('protocolo_id')->constrained('protocolos')->onDelete('cascade');
            $table->string('descricao'); // ex: "Taxa de Iniciação"
            $table->decimal('valor', 10, 2);
            $table->boolean('pago')->default(false);
            $table->date('data_pagamento')->nullable();
            $table->enum('forma_pagamento', ['dinheiro', 'pix', 'transferencia', 'cartao'])->nullable();
            $table->string('comprovante')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('protocolo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocolo_taxas');
    }
};
