<?php

namespace App\Services;

use App\Models\Protocolo;
use App\Models\Membro;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AfastamentoService
{
    /**
     * Processa o afastamento de um membro
     *
     * @param Protocolo $protocolo
     * @param User $aprovador
     * @param string|null $observacoes
     * @return array
     */
    public function processarAfastamento(
        Protocolo $protocolo,
        User $aprovador,
        ?string $observacoes = null
    ): array {
        DB::beginTransaction();

        try {
            // Validar protocolo
            if ($protocolo->tipo_protocolo !== 'afastamento') {
                throw new \Exception('Este não é um protocolo de afastamento');
            }

            // Buscar membro
            $membro = $protocolo->membro;

            if (!$membro) {
                throw new \Exception('Membro não encontrado no protocolo');
            }

            // Verificar se já está afastada
            if ($membro->status === 'afastada') {
                throw new \Exception('Membro já está afastada');
            }

            // Obter dados do protocolo
            $dadosJson = $protocolo->dados_json ?? [];
            $dataAfastamento = $dadosJson['data_afastamento'] ?? now()->toDateString();
            $motivoAfastamento = $dadosJson['motivo_afastamento'] ?? 'Não especificado';

            // Atualizar status do membro
            $membro->update([
                'status' => 'afastada',
                'motivo_afastamento' => $motivoAfastamento,
            ]);

            // Finalizar cargos ativos
            $this->finalizarCargosAtivos($membro, $dataAfastamento);

            // Log da operação
            Log::info('Membro afastado via protocolo', [
                'protocolo_id' => $protocolo->id,
                'membro_id' => $membro->id,
                'data_afastamento' => $dataAfastamento,
                'aprovador_id' => $aprovador->id,
            ]);

            DB::commit();

            return [
                'sucesso' => true,
                'mensagem' => "Membro {$membro->nome_completo} afastado com sucesso.",
                'membro_id' => $membro->id,
            ];

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Erro ao processar afastamento', [
                'protocolo_id' => $protocolo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao processar afastamento: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Finaliza todos os cargos ativos do membro
     *
     * @param Membro $membro
     * @param string $dataFim
     * @return void
     */
    protected function finalizarCargosAtivos(Membro $membro, string $dataFim): void
    {
        // Finalizar cargos de assembleia
        $cargosAssembleia = $membro->cargosAssembleia()
            ->where('ativo', true)
            ->get();

        foreach ($cargosAssembleia as $cargo) {
            $cargo->update([
                'ativo' => false,
                'data_fim' => $dataFim,
                'observacoes' => ($cargo->observacoes ? $cargo->observacoes . "\n" : '') .
                    "Cargo finalizado por afastamento do membro em {$dataFim}",
            ]);
        }

        // Finalizar cargos de conselho
        $cargosConselho = $membro->cargosConselho()
            ->where('ativo', true)
            ->get();

        foreach ($cargosConselho as $cargo) {
            $cargo->update([
                'ativo' => false,
                'data_fim' => $dataFim,
                'observacoes' => ($cargo->observacoes ? $cargo->observacoes . "\n" : '') .
                    "Cargo finalizado por afastamento do membro em {$dataFim}",
            ]);
        }

        Log::info('Cargos finalizados por afastamento', [
            'membro_id' => $membro->id,
            'cargos_assembleia' => $cargosAssembleia->count(),
            'cargos_conselho' => $cargosConselho->count(),
        ]);
    }

    /**
     * Valida os dados do protocolo de afastamento
     *
     * @param array $dados
     * @return array Erros de validação (vazio se válido)
     */
    public function validarDados(array $dados): array
    {
        $erros = [];

        // Validar membro_id
        if (empty($dados['membro_id'])) {
            $erros['membro_id'] = 'Membro é obrigatório';
        } else {
            $membro = Membro::find($dados['membro_id']);
            if (!$membro) {
                $erros['membro_id'] = 'Membro não encontrado';
            } elseif ($membro->status !== 'ativa') {
                $erros['membro_id'] = 'Apenas membros ativos podem ser afastados';
            }
        }

        // Validar data_afastamento
        if (empty($dados['data_afastamento'])) {
            $erros['data_afastamento'] = 'Data do afastamento é obrigatória';
        } else {
            $dataAfastamento = \Carbon\Carbon::parse($dados['data_afastamento']);
            if ($dataAfastamento->isFuture()) {
                $erros['data_afastamento'] = 'Data do afastamento não pode ser futura';
            }
        }

        // Validar motivo
        if (empty($dados['motivo_afastamento'])) {
            $erros['motivo_afastamento'] = 'Motivo do afastamento é obrigatório';
        }

        return $erros;
    }

    /**
     * Verifica se um membro pode ser afastado
     *
     * @param int $membroId
     * @return array
     */
    public function verificarElegibilidade(int $membroId): array
    {
        $membro = Membro::find($membroId);

        if (!$membro) {
            return [
                'elegivel' => false,
                'motivo' => 'Membro não encontrado',
            ];
        }

        if ($membro->status !== 'ativa') {
            return [
                'elegivel' => false,
                'motivo' => 'Membro não está ativa',
            ];
        }

        return [
            'elegivel' => true,
            'membro' => $membro,
        ];
    }

    /**
     * Obtém estatísticas de afastamentos
     *
     * @param int|null $assembleiaId
     * @return array
     */
    public function obterEstatisticas(?int $assembleiaId = null): array
    {
        $query = Protocolo::where('tipo_protocolo', 'afastamento');

        if ($assembleiaId) {
            $query->where('assembleia_id', $assembleiaId);
        }

        $total = $query->count();
        $aprovados = $query->where('status', 'concluido')->count();
        $rejeitados = $query->where('status', 'rejeitado')->count();
        $pendentes = $query->whereIn('status', ['pendente', 'em_analise'])->count();

        return [
            'total' => $total,
            'aprovados' => $aprovados,
            'rejeitados' => $rejeitados,
            'pendentes' => $pendentes,
            'taxa_aprovacao' => $total > 0 ? round(($aprovados / $total) * 100, 2) : 0,
        ];
    }
}
