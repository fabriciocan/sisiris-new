<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CargoGrandeAssembleia extends Model
{
    protected $table = 'cargos_grande_assembleia';

    protected $fillable = [
        'membro_id',
        'tipo_cargo_id',
        'data_inicio',
        'data_fim',
        'ativo',
        'atribuido_por',
        'observacoes',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean',
    ];

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
     * Relacionamento: Usuário que atribuiu o cargo
     */
    public function atribuidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atribuido_por');
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
     * Scope: Por tipo de cargo
     */
    public function scopePorTipo($query, $tipoCargoId)
    {
        return $query->where('tipo_cargo_id', $tipoCargoId);
    }
}
