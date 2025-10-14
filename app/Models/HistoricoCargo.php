<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoCargo extends Model
{
    protected $table = 'historico_cargos';

    public $timestamps = false;

    protected $fillable = [
        'membro_id',
        'tipo_cargo_id',
        'cargo_assembleia_id',
        'cargo_grande_assembleia_id',
        'assembleia_id',
        'semestre',
        'tipo_historico',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relacionamento: Histórico pertence a um membro
     */
    public function membro(): BelongsTo
    {
        return $this->belongsTo(Membro::class);
    }

    /**
     * Relacionamento: Histórico é de um tipo de cargo
     */
    public function tipoCargo(): BelongsTo
    {
        return $this->belongsTo(TipoCargoAssembleia::class, 'tipo_cargo_id');
    }

    /**
     * Relacionamento: Histórico pode estar ligado a um cargo de assembleia
     */
    public function cargoAssembleia(): BelongsTo
    {
        return $this->belongsTo(CargoAssembleia::class);
    }

    /**
     * Relacionamento: Histórico pode estar ligado a um cargo de grande assembleia
     */
    public function cargoGrandeAssembleia(): BelongsTo
    {
        return $this->belongsTo(CargoGrandeAssembleia::class);
    }

    /**
     * Relacionamento: Histórico pode estar ligado a uma assembleia
     */
    public function assembleia(): BelongsTo
    {
        return $this->belongsTo(Assembleia::class);
    }

    /**
     * Scope: Por membro
     */
    public function scopePorMembro($query, $membroId)
    {
        return $query->where('membro_id', $membroId);
    }

    /**
     * Scope: Por semestre
     */
    public function scopePorSemestre($query, $semestre)
    {
        return $query->where('semestre', $semestre);
    }

    /**
     * Scope: Apenas histórico de assembleia
     */
    public function scopeAssembleia($query)
    {
        return $query->where('tipo_historico', 'assembleia');
    }

    /**
     * Scope: Apenas histórico de grande assembleia
     */
    public function scopeGrandeAssembleia($query)
    {
        return $query->where('tipo_historico', 'grande_assembleia');
    }
}
