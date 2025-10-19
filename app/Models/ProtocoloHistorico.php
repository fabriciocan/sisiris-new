<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtocoloHistorico extends Model
{
    protected $table = 'protocolo_historico';

    public $timestamps = false;

    /**
     * Tipos de ação para histórico de protocolos
     */
    public const ACAO_CRIACAO = 'criacao';
    public const ACAO_EDICAO = 'edicao';
    public const ACAO_ENVIO_APROVACAO = 'envio_aprovacao';
    public const ACAO_APROVACAO = 'aprovacao';
    public const ACAO_REJEICAO = 'rejeicao';
    public const ACAO_DEFINICAO_TAXA = 'definicao_taxa';
    public const ACAO_PAGAMENTO = 'pagamento';
    public const ACAO_CONCLUSAO = 'conclusao';
    public const ACAO_MUDANCA_ETAPA = 'mudanca_etapa';
    public const ACAO_MUDANCA_STATUS = 'mudanca_status';
    public const ACAO_ADICAO_MEMBRO = 'adicao_membro';
    public const ACAO_REMOCAO_MEMBRO = 'remocao_membro';
    public const ACAO_ANEXO_ADICIONADO = 'anexo_adicionado';
    public const ACAO_ANEXO_REMOVIDO = 'anexo_removido';
    public const ACAO_CANCELAMENTO = 'cancelamento';

    /**
     * Labels para cada tipo de ação
     */
    public const ACOES_LABELS = [
        self::ACAO_CRIACAO => 'Protocolo criado',
        self::ACAO_EDICAO => 'Protocolo editado',
        self::ACAO_ENVIO_APROVACAO => 'Enviado para aprovação',
        self::ACAO_APROVACAO => 'Aprovado',
        self::ACAO_REJEICAO => 'Rejeitado',
        self::ACAO_DEFINICAO_TAXA => 'Taxa definida',
        self::ACAO_PAGAMENTO => 'Comprovante de pagamento anexado',
        self::ACAO_CONCLUSAO => 'Protocolo concluído',
        self::ACAO_MUDANCA_ETAPA => 'Etapa alterada',
        self::ACAO_MUDANCA_STATUS => 'Status alterado',
        self::ACAO_ADICAO_MEMBRO => 'Membro adicionado',
        self::ACAO_REMOCAO_MEMBRO => 'Membro removido',
        self::ACAO_ANEXO_ADICIONADO => 'Anexo adicionado',
        self::ACAO_ANEXO_REMOVIDO => 'Anexo removido',
        self::ACAO_CANCELAMENTO => 'Protocolo cancelado',
    ];

    protected $fillable = [
        'protocolo_id',
        'user_id',
        'acao',
        'descricao',
        'status_anterior',
        'status_novo',
        'etapa_anterior',
        'etapa_nova',
        'dados_anteriores',
        'dados_novos',
        'comentario',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'dados_anteriores' => 'array',
        'dados_novos' => 'array',
    ];

    /**
     * Relacionamento: Histórico pertence a um protocolo
     */
    public function protocolo(): BelongsTo
    {
        return $this->belongsTo(Protocolo::class);
    }

    /**
     * Relacionamento: Histórico foi criado por um usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtém o label da ação
     */
    public function getAcaoLabelAttribute(): string
    {
        return self::ACOES_LABELS[$this->acao] ?? ucfirst(str_replace('_', ' ', $this->acao));
    }

    /**
     * Obtém o nome do usuário que realizou a ação
     */
    public function getNomeUsuarioAttribute(): string
    {
        return $this->user?->name ?? 'Sistema';
    }

    /**
     * Scope: Filtrar por tipo de ação
     */
    public function scopePorAcao($query, string $acao)
    {
        return $query->where('acao', $acao);
    }

    /**
     * Scope: Filtrar por período
     */
    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('created_at', [$dataInicio, $dataFim]);
    }

    /**
     * Scope: Ordenar por data decrescente (mais recente primeiro)
     */
    public function scopeMaisRecentes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Ordenar por data crescente (mais antigo primeiro)
     */
    public function scopeMaisAntigos($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * Verifica se houve mudança de status
     */
    public function hasStatusChange(): bool
    {
        return !is_null($this->status_anterior) && !is_null($this->status_novo);
    }

    /**
     * Verifica se houve mudança de etapa
     */
    public function hasEtapaChange(): bool
    {
        return !is_null($this->etapa_anterior) && !is_null($this->etapa_nova);
    }

    /**
     * Verifica se tem dados anteriores
     */
    public function hasDadosAnteriores(): bool
    {
        return !is_null($this->dados_anteriores) && !empty($this->dados_anteriores);
    }

    /**
     * Verifica se tem dados novos
     */
    public function hasDadosNovos(): bool
    {
        return !is_null($this->dados_novos) && !empty($this->dados_novos);
    }

    /**
     * Obtém o diff entre dados anteriores e novos
     */
    public function getDiff(): array
    {
        if (!$this->hasDadosAnteriores() || !$this->hasDadosNovos()) {
            return [];
        }

        $diff = [];
        $anterior = $this->dados_anteriores;
        $novo = $this->dados_novos;

        foreach ($novo as $key => $value) {
            if (!isset($anterior[$key]) || $anterior[$key] !== $value) {
                $diff[$key] = [
                    'anterior' => $anterior[$key] ?? null,
                    'novo' => $value,
                ];
            }
        }

        return $diff;
    }

    /**
     * Formata a descrição completa do histórico
     */
    public function getDescricaoCompletaAttribute(): string
    {
        $parts = [$this->acao_label];

        if ($this->hasStatusChange()) {
            $parts[] = "Status: {$this->status_anterior} → {$this->status_novo}";
        }

        if ($this->hasEtapaChange()) {
            $parts[] = "Etapa: {$this->etapa_anterior} → {$this->etapa_nova}";
        }

        if ($this->descricao) {
            $parts[] = $this->descricao;
        }

        if ($this->comentario) {
            $parts[] = "Comentário: {$this->comentario}";
        }

        return implode(' | ', $parts);
    }

    /**
     * Boot do modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Definir created_at automaticamente
        static::creating(function ($model) {
            if (!$model->created_at) {
                $model->created_at = now();
            }
        });
    }
}
