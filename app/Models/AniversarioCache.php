<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AniversarioCache extends Model
{
    protected $table = 'aniversarios_cache';

    public $timestamps = false;

    protected $fillable = [
        'membro_id',
        'assembleia_id',
        'tipo',
        'mes',
        'dia',
        'updated_at',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento: Cache pertence a um membro
     */
    public function membro(): BelongsTo
    {
        return $this->belongsTo(Membro::class);
    }

    /**
     * Relacionamento: Cache pertence a uma assembleia
     */
    public function assembleia(): BelongsTo
    {
        return $this->belongsTo(Assembleia::class);
    }

    /**
     * Scope: Por mês
     */
    public function scopePorMes($query, $mes)
    {
        return $query->where('mes', $mes);
    }

    /**
     * Scope: Por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope: Por assembleia
     */
    public function scopePorAssembleia($query, $assembleiaId)
    {
        return $query->where('assembleia_id', $assembleiaId);
    }

    /**
     * Scope: Aniversariantes do dia
     */
    public function scopeDoDia($query, $dia = null, $mes = null)
    {
        $dia = $dia ?? now()->day;
        $mes = $mes ?? now()->month;

        return $query->where('mes', $mes)->where('dia', $dia);
    }
}
