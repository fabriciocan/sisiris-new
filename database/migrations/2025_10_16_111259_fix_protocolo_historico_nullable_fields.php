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
        Schema::table('protocolo_historico', function (Blueprint $table) {
            // Tornar campos nullable
            $table->string('status_anterior')->nullable()->change();
            $table->string('status_novo')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocolo_historico', function (Blueprint $table) {
            // Reverter para NOT NULL
            $table->string('status_anterior')->nullable(false)->change();
            $table->string('status_novo')->nullable(false)->change();
        });
    }
};
