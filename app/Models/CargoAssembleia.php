<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CargoAssembleia extends Model
{
    protected $table = 'cargos_assembleia';

    protected $fillable = [
        'assembleia_id',
        'membro_id',
        'tipo_cargo_id',
        'data_inicio',
        'data_fim',
        'ativo',
        'observacoes',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento: Cargo pertence a uma assembleia
     */
    public function assembleia(): BelongsTo
    {
        return $this->belongsTo(Assembleia::class);
    }

    /**
     * Relacionamento: Cargo pertence a um membro
     */
    public function membro(): BelongsTo
    {
        return $this->belongsTo(Membro::class);
    }

    /**
     * Relacionamento: Cargo é de um tipo específico
     */
    public function tipoCargo(): BelongsTo
    {
        return $this->belongsTo(TipoCargoAssembleia::class, 'tipo_cargo_id');
    }

    /**
     * Relacionamento: Histórico deste cargo
     */
    public function historicos(): HasMany
    {
        return $this->hasMany(HistoricoCargo::class);
    }

    /**
     * Scope: Cargos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope: Cargos vigentes (sem data fim ou data fim futura)
     */
    public function scopeVigentes($query)
    {
        return $query->where('ativo', true)
            ->where(function ($q) {
                $q->whereNull('data_fim')
                  ->orWhere('data_fim', '>=', now());
            });
    }

    /**
     * Scope: Por assembleia
     */
    public function scopePorAssembleia($query, $assembleiaId)
    {
        return $query->where('assembleia_id', $assembleiaId);
    }

    /**
     * Scope: Por tipo de cargo
     */
    public function scopePorTipo($query, $tipoCargoId)
    {
        return $query->where('tipo_cargo_id', $tipoCargoId);
    }
}
