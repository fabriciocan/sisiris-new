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
        Schema::table('cargos_assembleia', function (Blueprint $table) {
            // Add UUID primary key support
            $table->uuid('uuid')->nullable()->after('id');
            
            // Add protocol tracking fields
            $table->foreignUuid('protocolo_id')->nullable()->after('ativo')
                ->constrained('protocolos')->onDelete('set null');
            $table->foreignUuid('atribuido_por')->nullable()->after('protocolo_id')
                ->constrained('users')->onDelete('set null');
            
            // Add soft deletes
            $table->softDeletes()->after('updated_at');
            
            // Add indexes
            $table->index('protocolo_id');
            $table->index('atribuido_por');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargos_assembleia', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['protocolo_id']);
            $table->dropForeign(['atribuido_por']);
            $table->dropIndex(['protocolo_id']);
            $table->dropIndex(['atribuido_por']);
            $table->dropIndex(['deleted_at']);
            $table->dropColumn(['uuid', 'protocolo_id', 'atribuido_por']);
        });
    }
};
