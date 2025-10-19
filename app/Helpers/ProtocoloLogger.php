<?php

namespace App\Helpers;

use App\Models\Protocolo;
use App\Models\ProtocoloHistorico;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProtocoloLogger
{
    /**
     * Registra uma ação no histórico do protocolo
     */
    public static function log(
        Protocolo $protocolo,
        string $acao,
        ?string $descricao = null,
        ?array $dadosAnteriores = null,
        ?array $dadosNovos = null,
        ?string $comentario = null,
        ?User $user = null
    ): ProtocoloHistorico {
        $user = $user ?? Auth::user();

        // Usar solicitante_id do protocolo como fallback se não houver usuário autenticado
        $userId = $user?->id ?? $protocolo->solicitante_id;

        $dados = [
            'protocolo_id' => $protocolo->id,
            'user_id' => $userId,
            'acao' => $acao,
            'descricao' => $descricao,
            'dados_anteriores' => $dadosAnteriores,
            'dados_novos' => $dadosNovos,
            'comentario' => $comentario,
        ];

        return ProtocoloHistorico::create($dados);
    }

    /**
     * Registra criação de protocolo
     */
    public static function logCriacao(Protocolo $protocolo, ?User $user = null): ProtocoloHistorico
    {
        return self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_CRIACAO,
            "Protocolo {$protocolo->numero_protocolo} criado",
            null,
            $protocolo->toArray(),
            null,
            $user
        );
    }

    /**
     * Registra edição de protocolo
     */
    public static function logEdicao(
        Protocolo $protocolo,
        array $dadosAnteriores,
        array $dadosNovos,
        ?string $descricao = null,
        ?User $user = null
    ): ProtocoloHistorico {
        return self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_EDICAO,
            $descricao ?? 'Protocolo editado',
            $dadosAnteriores,
            $dadosNovos,
            null,
            $user
        );
    }

    /**
     * Registra envio para aprovação
     */
    public static function logEnvioAprovacao(
        Protocolo $protocolo,
        ?string $descricao = null,
        ?User $user = null
    ): ProtocoloHistorico {
        return self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_ENVIO_APROVACAO,
            $descricao ?? 'Protocolo enviado para aprovação',
            null,
            null,
            null,
            $user
        );
    }

    /**
     * Registra aprovação de protocolo
     */
    public static function logAprovacao(
        Protocolo $protocolo,
        ?string $comentario = null,
        ?User $user = null
    ): ProtocoloHistorico {
        return self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_APROVACAO,
            'Protocolo aprovado',
            null,
            null,
            $comentario,
            $user
        );
    }

    /**
     * Registra rejeição de protocolo
     */
    public static function logRejeicao(
        Protocolo $protocolo,
        string $motivoRejeicao,
        ?User $user = null
    ): ProtocoloHistorico {
        return self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_REJEICAO,
            'Protocolo rejeitado',
            null,
            null,
            $motivoRejeicao,
            $user
        );
    }

    /**
     * Registra definição de taxa
     */
    public static function logDefinicaoTaxa(
        Protocolo $protocolo,
        float $valorTaxa,
        ?User $user = null
    ): ProtocoloHistorico {
        return self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_DEFINICAO_TAXA,
            "Taxa definida: R$ " . number_format($valorTaxa, 2, ',', '.'),
            null,
            ['valor_taxa' => $valorTaxa],
            null,
            $user
        );
    }

    /**
     * Registra anexação de comprovante de pagamento
     */
    public static function logPagamento(
        Protocolo $protocolo,
        string $comprovanteUrl,
        ?User $user = null
    ): ProtocoloHistorico {
        return self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_PAGAMENTO,
            'Comprovante de pagamento anexado',
            null,
            ['comprovante_url' => $comprovanteUrl],
            null,
            $user
        );
    }

    /**
     * Registra conclusão de protocolo
     */
    public static function logConclusao(
        Protocolo $protocolo,
        ?string $descricao = null,
        ?User $user = null
    ): ProtocoloHistorico {
        return self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_CONCLUSAO,
            $descricao ?? 'Protocolo concluído',
            null,
            null,
            null,
            $user
        );
    }

    /**
     * Registra mudança de etapa
     */
    public static function logMudancaEtapa(
        Protocolo $protocolo,
        string $etapaAnterior,
        string $etapaNova,
        ?string $descricao = null,
        ?User $user = null
    ): ProtocoloHistorico {
        $historico = self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_MUDANCA_ETAPA,
            $descricao ?? 'Etapa do protocolo alterada',
            null,
            null,
            null,
            $user
        );

        $historico->update([
            'etapa_anterior' => $etapaAnterior,
            'etapa_nova' => $etapaNova,
        ]);

        return $historico;
    }

    /**
     * Registra mudança de status
     */
    public static function logMudancaStatus(
        Protocolo $protocolo,
        string $statusAnterior,
        string $statusNovo,
        ?string $descricao = null,
        ?User $user = null
    ): ProtocoloHistorico {
        $historico = self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_MUDANCA_STATUS,
            $descricao ?? 'Status do protocolo alterado',
            null,
            null,
            null,
            $user
        );

        $historico->update([
            'status_anterior' => $statusAnterior,
            'status_novo' => $statusNovo,
        ]);

        return $historico;
    }

    /**
     * Registra adição de membro ao protocolo
     */
    public static function logAdicaoMembro(
        Protocolo $protocolo,
        string $nomeMembro,
        ?array $dadosMembro = null,
        ?User $user = null
    ): ProtocoloHistorico {
        return self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_ADICAO_MEMBRO,
            "Membro adicionado: {$nomeMembro}",
            null,
            $dadosMembro,
            null,
            $user
        );
    }

    /**
     * Registra remoção de membro do protocolo
     */
    public static function logRemocaoMembro(
        Protocolo $protocolo,
        string $nomeMembro,
        ?array $dadosMembro = null,
        ?User $user = null
    ): ProtocoloHistorico {
        return self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_REMOCAO_MEMBRO,
            "Membro removido: {$nomeMembro}",
            $dadosMembro,
            null,
            null,
            $user
        );
    }

    /**
     * Registra adição de anexo
     */
    public static function logAnexoAdicionado(
        Protocolo $protocolo,
        string $nomeArquivo,
        ?User $user = null
    ): ProtocoloHistorico {
        return self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_ANEXO_ADICIONADO,
            "Anexo adicionado: {$nomeArquivo}",
            null,
            ['arquivo' => $nomeArquivo],
            null,
            $user
        );
    }

    /**
     * Registra remoção de anexo
     */
    public static function logAnexoRemovido(
        Protocolo $protocolo,
        string $nomeArquivo,
        ?User $user = null
    ): ProtocoloHistorico {
        return self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_ANEXO_REMOVIDO,
            "Anexo removido: {$nomeArquivo}",
            ['arquivo' => $nomeArquivo],
            null,
            null,
            $user
        );
    }

    /**
     * Registra cancelamento de protocolo
     */
    public static function logCancelamento(
        Protocolo $protocolo,
        string $motivo,
        ?User $user = null
    ): ProtocoloHistorico {
        return self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_CANCELAMENTO,
            'Protocolo cancelado',
            null,
            null,
            $motivo,
            $user
        );
    }

    /**
     * Registra mudança completa de status e etapa
     */
    public static function logTransicao(
        Protocolo $protocolo,
        ?string $statusAnterior,
        string $statusNovo,
        ?string $etapaAnterior,
        string $etapaNova,
        ?string $descricao = null,
        ?string $comentario = null,
        ?User $user = null
    ): ProtocoloHistorico {
        $historico = self::log(
            $protocolo,
            ProtocoloHistorico::ACAO_MUDANCA_ETAPA,
            $descricao ?? 'Protocolo transitou para nova etapa',
            null,
            null,
            $comentario,
            $user
        );

        $historico->update([
            'status_anterior' => $statusAnterior,
            'status_novo' => $statusNovo,
            'etapa_anterior' => $etapaAnterior,
            'etapa_nova' => $etapaNova,
        ]);

        return $historico;
    }

    /**
     * Obtém todos os logs de um protocolo ordenados por data
     */
    public static function getTimeline(Protocolo $protocolo, bool $maisRecente = true)
    {
        $query = $protocolo->historico()->with('user');

        return $maisRecente
            ? $query->maisRecentes()->get()
            : $query->maisAntigos()->get();
    }

    /**
     * Obtém logs de um tipo específico de ação
     */
    public static function getLogsPorAcao(Protocolo $protocolo, string $acao)
    {
        return $protocolo->historico()
            ->porAcao($acao)
            ->with('user')
            ->maisRecentes()
            ->get();
    }

    /**
     * Obtém logs de um período específico
     */
    public static function getLogsPorPeriodo(Protocolo $protocolo, $dataInicio, $dataFim)
    {
        return $protocolo->historico()
            ->porPeriodo($dataInicio, $dataFim)
            ->with('user')
            ->maisRecentes()
            ->get();
    }

    /**
     * Verifica se protocolo tem logs de um tipo específico
     */
    public static function hasLogTipo(Protocolo $protocolo, string $acao): bool
    {
        return $protocolo->historico()->porAcao($acao)->exists();
    }

    /**
     * Obtém o último log de um tipo específico
     */
    public static function getUltimoLogTipo(Protocolo $protocolo, string $acao): ?ProtocoloHistorico
    {
        return $protocolo->historico()
            ->porAcao($acao)
            ->maisRecentes()
            ->first();
    }
}
