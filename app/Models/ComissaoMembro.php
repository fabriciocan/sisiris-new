<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComissaoMembro extends Model
{
    protected $table = 'comissao_membros';

    protected $fillable = [
        'comissao_id',
        'user_id',
        'cargo',
        'data_inicio',
        'data_fim',
        'ativo',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento: Membro da comissão pertence a uma comissão
     */
    public function comissao(): BelongsTo
    {
        return $this->belongsTo(Comissao::class);
    }

    /**
     * Relacionamento: Membro da comissão é um usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Membros ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
