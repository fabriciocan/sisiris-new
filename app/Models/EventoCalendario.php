<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventoCalendario extends Model
{
    use SoftDeletes;

    protected $table = 'eventos_calendario';

    protected $fillable = [
        'assembleia_id',
        'titulo',
        'descricao',
        'tipo',
        'data_inicio',
        'data_fim',
        'local',
        'endereco',
        'publico',
        'criado_por',
        'cor_evento',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'publico' => 'boolean',
    ];

    /**
     * Relacionamento: Evento pode pertencer a uma assembleia
     */
    public function assembleia(): BelongsTo
    {
        return $this->belongsTo(Assembleia::class);
    }

    /**
     * Relacionamento: Evento foi criado por um usuário
     */
    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    /**
     * Scope: Eventos públicos
     */
    public function scopePublicos($query)
    {
        return $query->where('publico', true);
    }

    /**
     * Scope: Eventos privados
     */
    public function scopePrivados($query)
    {
        return $query->where('publico', false);
    }

    /**
     * Scope: Por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope: Eventos futuros
     */
    public function scopeFuturos($query)
    {
        return $query->where('data_inicio', '>=', now());
    }

    /**
     * Scope: Eventos passados
     */
    public function scopePassados($query)
    {
        return $query->where('data_inicio', '<', now());
    }

    /**
     * Scope: Eventos do mês
     */
    public function scopeDoMes($query, $mes = null, $ano = null)
    {
        $mes = $mes ?? now()->month;
        $ano = $ano ?? now()->year;

        return $query->whereMonth('data_inicio', $mes)
                     ->whereYear('data_inicio', $ano);
    }

    /**
     * Scope: Por assembleia
     */
    public function scopePorAssembleia($query, $assembleiaId)
    {
        return $query->where('assembleia_id', $assembleiaId);
    }

    /**
     * Scope: Eventos da jurisdição (sem assembleia específica)
     */
    public function scopeDaJurisdicao($query)
    {
        return $query->whereNull('assembleia_id');
    }
}
