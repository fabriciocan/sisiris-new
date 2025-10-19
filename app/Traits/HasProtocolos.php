<?php

namespace App\Traits;

use App\Models\Protocolo;
use App\Models\ProtocoloHistorico;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasProtocolos
{
    /**
     * Relacionamento: Protocolos criados pelo usuário/membro
     */
    public function protocolosCriados(): HasMany
    {
        return $this->hasMany(Protocolo::class, 'solicitante_id');
    }

    /**
     * Relacionamento: Protocolos relacionados ao membro
     */
    public function protocolosRelacionados(): HasMany
    {
        return $this->hasMany(Protocolo::class, 'membro_id');
    }

    /**
     * Obter protocolos pendentes
     */
    public function protocolosPendentes()
    {
        return $this->protocolosCriados()->where('status', 'pendente');
    }

    /**
     * Obter protocolos em análise
     */
    public function protocolosEmAnalise()
    {
        return $this->protocolosCriados()->where('status', 'em_analise');
    }

    /**
     * Obter protocolos aprovados
     */
    public function protocolosAprovados()
    {
        return $this->protocolosCriados()->where('status', 'aprovado');
    }

    /**
     * Criar novo protocolo
     */
    public function criarProtocolo(array $dados): Protocolo
    {
        // Gerar número do protocolo
        $ano = now()->year;
        $ultimoNumero = Protocolo::where('numero_protocolo', 'like', "PR-{$ano}-%")
            ->count();
        $proximoNumero = str_pad($ultimoNumero + 1, 3, '0', STR_PAD_LEFT);
        
        $dados['numero_protocolo'] = "PR-{$ano}-{$proximoNumero}";
        $dados['solicitante_id'] = $this->id;
        $dados['data_solicitacao'] = now();
        $dados['status'] = 'pendente';

        $protocolo = Protocolo::create($dados);

        // Registrar no histórico
        ProtocoloHistorico::create([
            'protocolo_id' => $protocolo->id,
            'user_id' => $this->id,
            'status_anterior' => null,
            'status_novo' => 'pendente',
            'comentario' => 'Protocolo criado'
        ]);

        return $protocolo;
    }

    /**
     * Atualizar status do protocolo
     */
    public function atualizarStatusProtocolo(int $protocoloId, string $novoStatus, string $comentario = null): bool
    {
        $protocolo = Protocolo::find($protocoloId);
        
        if ($protocolo) {
            $statusAnterior = $protocolo->status;
            
            $protocolo->update(['status' => $novoStatus]);

            // Registrar no histórico
            ProtocoloHistorico::create([
                'protocolo_id' => $protocolo->id,
                'user_id' => $this->id,
                'status_anterior' => $statusAnterior,
                'status_novo' => $novoStatus,
                'comentario' => $comentario
            ]);

            return true;
        }

        return false;
    }

    /**
     * Verificar se pode criar protocolos
     */
    public function podecriarProtocolos(): bool
    {
        // Membros da jurisdição e admins de assembleia podem criar protocolos
        if (method_exists($this, 'hasRole')) {
            return $this->hasRole(['membro_jurisdicao', 'admin_assembleia']);
        }

        return false;
    }

    /**
     * Verificar se pode aprovar protocolos
     */
    public function podeAprovarProtocolos(): bool
    {
        // Apenas membros da jurisdição podem aprovar protocolos
        if (method_exists($this, 'hasRole')) {
            return $this->hasRole('membro_jurisdicao');
        }

        return false;
    }
}