<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class ProtocoloMembro extends Model
{
    use HasUuid;

    protected $table = 'protocolo_membros';

    protected $fillable = [
        'protocolo_id',
        'membro_id',
        'presente_cerimonia',
        'observacoes',
        'dados_especificos',
    ];

    protected $casts = [
        'presente_cerimonia' => 'boolean',
        'dados_especificos' => 'array',
    ];

    /**
     * Relacionamento: ProtocoloMembro pertence a um protocolo
     */
    public function protocolo(): BelongsTo
    {
        return $this->belongsTo(Protocolo::class);
    }

    /**
     * Relacionamento: ProtocoloMembro pertence a um membro
     */
    public function membro(): BelongsTo
    {
        return $this->belongsTo(Membro::class);
    }

    /**
     * Scope: Membros presentes na cerimônia
     */
    public function scopePresentesCerimonia($query)
    {
        return $query->where('presente_cerimonia', true);
    }

    /**
     * Scope: Membros ausentes na cerimônia
     */
    public function scopeAusentesCerimonia($query)
    {
        return $query->where('presente_cerimonia', false);
    }

    /**
     * Scope: Por protocolo
     */
    public function scopePorProtocolo($query, $protocoloId)
    {
        return $query->where('protocolo_id', $protocoloId);
    }

    /**
     * Scope: Por membro
     */
    public function scopePorMembro($query, $membroId)
    {
        return $query->where('membro_id', $membroId);
    }
}