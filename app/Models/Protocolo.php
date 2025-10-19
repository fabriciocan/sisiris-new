<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Services\ProtocoloWorkflow;

class Protocolo extends Model
{
    use SoftDeletes, HasUuid;

    protected $table = 'protocolos';

    protected $fillable = [
        'numero_protocolo',
        'assembleia_id',
        'tipo_protocolo', // Renamed from 'tipo'
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
        // New workflow fields
        'etapa_atual',
        'data_cerimonia',
        'valor_taxa',
        'comprovante_pagamento',
        'feedback_rejeicao',
        'aprovado_por',
        'data_aprovacao',
        'dados_membros',
        'configuracao_etapas',
    ];

    protected $casts = [
        'data_solicitacao' => 'datetime',
        'data_conclusao' => 'datetime',
        'data_cerimonia' => 'date',
        'data_aprovacao' => 'datetime',
        'valor_taxa' => 'decimal:2',
        'dados_json' => 'array',
        'dados_membros' => 'array',
        'configuracao_etapas' => 'array',
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
     * Relacionamento: Protocolo tem um aprovador
     */
    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    /**
     * Relacionamento: Protocolo tem muitos membros através da tabela pivot
     */
    public function membros(): BelongsToMany
    {
        return $this->belongsToMany(Membro::class, 'protocolo_membros')
            ->withPivot(['presente_cerimonia', 'observacoes', 'dados_especificos'])
            ->withTimestamps();
    }

    /**
     * Relacionamento: Protocolo tem muitos registros na tabela pivot
     */
    public function protocoloMembros(): HasMany
    {
        return $this->hasMany(ProtocoloMembro::class);
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

    /**
     * Scope: Por etapa atual
     */
    public function scopePorEtapa($query, $etapa)
    {
        return $query->where('etapa_atual', $etapa);
    }

    /**
     * Scope: Aguardando aprovação
     */
    public function scopeAguardandoAprovacao($query)
    {
        return $query->whereIn('etapa_atual', ['aprovacao', 'aprovacao_honrarias', 'aprovacao_final']);
    }

    /**
     * Scope: Aguardando pagamento
     */
    public function scopeAguardandoPagamento($query)
    {
        return $query->where('etapa_atual', 'aguardando_pagamento');
    }

    /**
     * Get workflow instance for this protocol
     */
    public function getWorkflow(): ProtocoloWorkflow
    {
        return new ProtocoloWorkflow($this);
    }

    /**
     * Check if protocol can transition to a specific step
     */
    public function canTransitionTo(string $targetStep): bool
    {
        return $this->getWorkflow()->canTransitionTo($targetStep);
    }

    /**
     * Check if user can perform action on current step
     */
    public function canUserPerformAction(User $user): bool
    {
        return $this->getWorkflow()->canUserPerformAction($user);
    }

    /**
     * Transition to next step
     */
    public function transitionTo(string $targetStep, User $user, array $data = []): bool
    {
        return $this->getWorkflow()->transitionTo($targetStep, $user, $data);
    }

    /**
     * Get current step configuration
     */
    public function getCurrentStep(): array
    {
        return $this->getWorkflow()->getCurrentStep();
    }

    /**
     * Get possible next steps
     */
    public function getNextSteps(): array
    {
        return $this->getWorkflow()->getNextSteps();
    }

    /**
     * Check if protocol is in final state
     */
    public function isInFinalState(): bool
    {
        return $this->getWorkflow()->isInFinalState();
    }

    /**
     * Get workflow progress percentage
     */
    public function getProgressPercentage(): int
    {
        return $this->getWorkflow()->getProgressPercentage();
    }

    /**
     * Initialize workflow for new protocol
     */
    public function initializeWorkflow(): void
    {
        $workflow = $this->getWorkflow();
        $initialStep = $workflow->getInitialStep();
        
        $this->update([
            'etapa_atual' => $initialStep,
            'configuracao_etapas' => $workflow->getAllSteps(),
        ]);
    }

    /**
     * Add member to protocol
     */
    public function addMembro(Membro $membro, array $pivotData = []): void
    {
        $this->membros()->attach($membro->id, $pivotData);
        
        // Update dados_membros array
        $dadosMembros = $this->dados_membros ?? [];
        $dadosMembros[] = [
            'membro_id' => $membro->id,
            'nome' => $membro->nome,
            'added_at' => now()->toISOString(),
        ];
        
        $this->update(['dados_membros' => $dadosMembros]);
    }

    /**
     * Remove member from protocol
     */
    public function removeMembro(Membro $membro): void
    {
        $this->membros()->detach($membro->id);
        
        // Update dados_membros array
        $dadosMembros = collect($this->dados_membros ?? [])
            ->reject(fn($item) => $item['membro_id'] === $membro->id)
            ->values()
            ->toArray();
        
        $this->update(['dados_membros' => $dadosMembros]);
    }

    /**
     * Get members present in ceremony
     */
    public function getMembrosPresentes()
    {
        return $this->membros()->wherePivot('presente_cerimonia', true)->get();
    }

    /**
     * Get members absent from ceremony
     */
    public function getMembrosAusentes()
    {
        return $this->membros()->wherePivot('presente_cerimonia', false)->get();
    }

    /**
     * Mark member as present in ceremony
     */
    public function marcarPresencaCerimonia(Membro $membro, bool $presente = true): void
    {
        $this->membros()->updateExistingPivot($membro->id, [
            'presente_cerimonia' => $presente,
        ]);
    }

    /**
     * Set ceremony attendance for multiple members
     */
    public function definirPresencaCerimonia(array $membrosPresentes): void
    {
        foreach ($this->membros as $membro) {
            $presente = in_array($membro->id, $membrosPresentes);
            $this->marcarPresencaCerimonia($membro, $presente);
        }
    }

    /**
     * Check if protocol requires ceremony
     */
    public function requiresCeremony(): bool
    {
        return in_array($this->tipo_protocolo, [
            'maioridade',
            'iniciacao',
            'homenageados_ano',
            'coracao_cores',
            'grande_cruz_cores',
        ]);
    }

    /**
     * Check if protocol requires payment
     */
    public function requiresPayment(): bool
    {
        return in_array($this->tipo_protocolo, [
            'homenageados_ano',
            'coracao_cores',
            'grande_cruz_cores',
        ]);
    }

    /**
     * Check if protocol has honors workflow
     */
    public function hasHonorsWorkflow(): bool
    {
        return in_array($this->tipo_protocolo, [
            'homenageados_ano',
            'coracao_cores',
            'grande_cruz_cores',
        ]);
    }
}
