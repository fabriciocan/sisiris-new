<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class Protocolo extends Model
{
    use SoftDeletes, HasUuid;

    protected $table = 'protocolos';

    protected $fillable = [
        'numero_protocolo',
        'assembleia_id',
        'tipo',
        'titulo',
        'descricao',
        'membro_id',
        'solicitante_id',
        'status',
        'prioridade',
        'data_solicitacao',
        'data_conclusao',
        'observacoes',
        'dados_json',
    ];

    protected $casts = [
        'data_solicitacao' => 'datetime',
        'data_conclusao' => 'datetime',
        'dados_json' => 'array',
    ];

    /**
     * Relacionamento: Protocolo pertence a uma assembleia
     */
    public function assembleia(): BelongsTo
    {
        return $this->belongsTo(Assembleia::class);
    }

    /**
     * Relacionamento: Protocolo pode estar associado a um membro
     */
    public function membro(): BelongsTo
    {
        return $this->belongsTo(Membro::class);
    }

    /**
     * Relacionamento: Protocolo tem um solicitante (usuário)
     */
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    /**
     * Relacionamento: Protocolo tem vários registros de histórico
     */
    public function historico(): HasMany
    {
        return $this->hasMany(ProtocoloHistorico::class);
    }

    /**
     * Relacionamento: Protocolo tem vários anexos
     */
    public function anexos(): HasMany
    {
        return $this->hasMany(ProtocoloAnexo::class);
    }

    /**
     * Relacionamento: Protocolo tem várias taxas
     */
    public function taxas(): HasMany
    {
        return $this->hasMany(ProtocoloTaxa::class);
    }

    /**
     * Scope: Por status
     */
    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope: Protocolos pendentes
     */
    public function scopePendentes($query)
    {
        return $query->whereIn('status', ['rascunho', 'pendente', 'em_analise']);
    }

    /**
     * Scope: Protocolos concluídos
     */
    public function scopeConcluidos($query)
    {
        return $query->whereIn('status', ['aprovado', 'concluido']);
    }
}
