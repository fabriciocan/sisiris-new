<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtocoloHistorico extends Model
{
    protected $table = 'protocolo_historico';

    public $timestamps = false;

    protected $fillable = [
        'protocolo_id',
        'user_id',
        'status_anterior',
        'status_novo',
        'comentario',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relacionamento: Histórico pertence a um protocolo
     */
    public function protocolo(): BelongsTo
    {
        return $this->belongsTo(Protocolo::class);
    }

    /**
     * Relacionamento: Histórico foi criado por um usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
