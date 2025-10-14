<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jurisdicao extends Model
{
    use SoftDeletes;

    protected $table = 'jurisdicoes';

    protected $fillable = [
        'nome',
        'sigla',
        'email',
        'telefone',
        'endereco_completo',
        'ativa',
    ];

    protected $casts = [
        'ativa' => 'boolean',
    ];

    /**
     * Relacionamento: Uma jurisdição tem várias assembleias
     */
    public function assembleias(): HasMany
    {
        return $this->hasMany(Assembleia::class);
    }

    /**
     * Scope: Apenas jurisdições ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativa', true);
    }
}
