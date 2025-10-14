<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoCargoAssembleia extends Model
{
    use SoftDeletes;

    protected $table = 'tipos_cargos_assembleia';

    protected $fillable = [
        'nome',
        'categoria',
        'is_admin',
        'ordem',
        'ativo',
        'criado_por',
        'descricao',
        'acessos',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'ativo' => 'boolean',
        'acessos' => 'array',
    ];

    /**
     * Relacionamento: Tipo de cargo tem vários cargos de assembleia
     */
    public function cargosAssembleia(): HasMany
    {
        return $this->hasMany(CargoAssembleia::class, 'tipo_cargo_id');
    }

    /**
     * Relacionamento: Tipo de cargo tem vários cargos de grande assembleia
     */
    public function cargosGrandeAssembleia(): HasMany
    {
        return $this->hasMany(CargoGrandeAssembleia::class, 'tipo_cargo_id');
    }

    /**
     * Relacionamento: Histórico de cargos deste tipo
     */
    public function historicos(): HasMany
    {
        return $this->hasMany(HistoricoCargo::class, 'tipo_cargo_id');
    }

    /**
     * Scope: Apenas cargos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope: Cargos administrativos
     */
    public function scopeAdministrativos($query)
    {
        return $query->where('categoria', 'administrativo');
    }

    /**
     * Scope: Cargos de menina
     */
    public function scopeMeninas($query)
    {
        return $query->where('categoria', 'menina');
    }

    /**
     * Scope: Cargos de grande assembleia
     */
    public function scopeGrandeAssembleia($query)
    {
        return $query->where('categoria', 'grande_assembleia');
    }

    /**
     * Scope: Ordenado
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem');
    }
}
