<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketResposta extends Model
{
    protected $table = 'ticket_respostas';

    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'mensagem',
        'interno',
        'created_at',
    ];

    protected $casts = [
        'interno' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Relacionamento: Resposta pertence a um ticket
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relacionamento: Resposta foi criada por um usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Apenas respostas públicas
     */
    public function scopePublicas($query)
    {
        return $query->where('interno', false);
    }

    /**
     * Scope: Apenas respostas internas
     */
    public function scopeInternas($query)
    {
        return $query->where('interno', true);
    }
}
