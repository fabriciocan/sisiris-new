<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class Ticket extends Model
{
    use SoftDeletes, HasUuid;

    protected $fillable = [
        'numero_ticket',
        'assembleia_id',
        'comissao_id',
        'solicitante_id',
        'responsavel_id',
        'categoria',
        'assunto',
        'descricao',
        'prioridade',
        'status',
        'data_abertura',
        'data_primeira_resposta',
        'data_resolucao',
        'data_fechamento',
        'avaliacao',
        'comentario_avaliacao',
    ];

    protected $casts = [
        'data_abertura' => 'datetime',
        'data_primeira_resposta' => 'datetime',
        'data_resolucao' => 'datetime',
        'data_fechamento' => 'datetime',
        'avaliacao' => 'integer',
    ];

    /**
     * Relacionamento: Ticket pode estar associado a uma assembleia
     */
    public function assembleia(): BelongsTo
    {
        return $this->belongsTo(Assembleia::class);
    }

    /**
     * Relacionamento: Ticket pode estar associado a uma comissão
     */
    public function comissao(): BelongsTo
    {
        return $this->belongsTo(Comissao::class);
    }

    /**
     * Relacionamento: Ticket tem um solicitante
     */
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    /**
     * Relacionamento: Ticket pode ter um responsável
     */
    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    /**
     * Relacionamento: Ticket tem várias respostas
     */
    public function respostas(): HasMany
    {
        return $this->hasMany(TicketResposta::class);
    }

    /**
     * Relacionamento: Ticket tem várias respostas (alias para RelationManager)
     */
    public function ticketRespostas(): HasMany
    {
        return $this->hasMany(TicketResposta::class);
    }

    /**
     * Relacionamento: Ticket tem vários anexos
     */
    public function anexos(): HasMany
    {
        return $this->hasMany(TicketAnexo::class);
    }

    /**
     * Scope: Tickets abertos
     */
    public function scopeAbertos($query)
    {
        return $query->whereIn('status', ['aberto', 'em_atendimento', 'aguardando_resposta']);
    }

    /**
     * Scope: Tickets fechados
     */
    public function scopeFechados($query)
    {
        return $query->whereIn('status', ['resolvido', 'fechado']);
    }

    /**
     * Scope: Por status
     */
    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Por categoria
     */
    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope: Por prioridade
     */
    public function scopePorPrioridade($query, $prioridade)
    {
        return $query->where('prioridade', $prioridade);
    }
}
