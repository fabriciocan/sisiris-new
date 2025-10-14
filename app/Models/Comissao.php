<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Comissao extends Model
{
    protected $table = 'comissoes';

    protected $fillable = [
        'jurisdicao_id',
        'nome',
        'descricao',
        'ativa',
    ];

    protected $casts = [
        'ativa' => 'boolean',
    ];

    /**
     * Relacionamento: Comissão pertence a uma jurisdição
     */
    public function jurisdicao(): BelongsTo
    {
        return $this->belongsTo(Jurisdicao::class);
    }

    /**
     * Relacionamento: Comissão tem vários usuários (membros da comissão)
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'comissao_membros')
            ->withPivot('cargo', 'data_inicio', 'data_fim', 'ativo')
            ->withTimestamps();
    }

    /**
     * Scope: Comissões ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativa', true);
    }

    /**
     * Scope: Por jurisdição
     */
    public function scopePorJurisdicao($query, $jurisdicaoId)
    {
        return $query->where('jurisdicao_id', $jurisdicaoId);
    }
}
