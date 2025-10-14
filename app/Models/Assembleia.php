<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assembleia extends Model
{
    use SoftDeletes;

    protected $table = 'assembleias';

    protected $fillable = [
        'jurisdicao_id',
        'numero',
        'nome',
        'cidade',
        'estado',
        'endereco_completo',
        'data_fundacao',
        'email',
        'telefone',
        'ativa',
        'loja_patrocinadora',
    ];

    protected $casts = [
        'data_fundacao' => 'date',
        'ativa' => 'boolean',
    ];

    /**
     * Relacionamento: Assembleia pertence a uma jurisdição
     */
    public function jurisdicao(): BelongsTo
    {
        return $this->belongsTo(Jurisdicao::class);
    }

    /**
     * Relacionamento: Uma assembleia tem vários membros
     */
    public function membros(): HasMany
    {
        return $this->hasMany(Membro::class);
    }

    /**
     * Relacionamento: Uma assembleia tem vários cargos
     */
    public function cargos(): HasMany
    {
        return $this->hasMany(CargoAssembleia::class);
    }

    /**
     * Relacionamento: Uma assembleia tem várias comissões
     */
    public function comissoes(): HasMany
    {
        return $this->hasMany(Comissao::class);
    }

    /**
     * Scope: Apenas assembleias ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativa', true);
    }

    /**
     * Scope: Filtrar por jurisdição
     */
    public function scopePorJurisdicao($query, $jurisdicaoId)
    {
        return $query->where('jurisdicao_id', $jurisdicaoId);
    }

    /**
     * Scope: Filtrar por cidade
     */
    public function scopePorCidade($query, $cidade)
    {
        return $query->where('cidade', $cidade);
    }
}
