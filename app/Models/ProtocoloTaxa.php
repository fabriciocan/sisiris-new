<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtocoloTaxa extends Model
{
    protected $table = 'protocolo_taxas';

    protected $fillable = [
        'protocolo_id',
        'descricao',
        'valor',
        'pago',
        'data_pagamento',
        'forma_pagamento',
        'comprovante',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'pago' => 'boolean',
        'data_pagamento' => 'date',
    ];

    /**
     * Relacionamento: Taxa pertence a um protocolo
     */
    public function protocolo(): BelongsTo
    {
        return $this->belongsTo(Protocolo::class);
    }

    /**
     * Scope: Taxas pagas
     */
    public function scopePagas($query)
    {
        return $query->where('pago', true);
    }

    /**
     * Scope: Taxas pendentes
     */
    public function scopePendentes($query)
    {
        return $query->where('pago', false);
    }
}
