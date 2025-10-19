<?php

namespace App\Services;

use App\Models\Protocolo;
use App\Models\Assembleia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProtocoloService
{
    /**
     * Gerar próximo número de protocolo no formato PR-YYYY-NNN
     */
    public function gerarNumeroProtocolo(int $assembleiaId): string
    {
        $assembleia = Assembleia::findOrFail($assembleiaId);
        $ano = Carbon::now()->year;
        
        // Buscar o último protocolo do ano atual para esta assembleia
        $ultimoProtocolo = Protocolo::where('assembleia_id', $assembleiaId)
            ->where('numero', 'like', "PR-{$ano}-%")
            ->orderBy('numero', 'desc')
            ->first();
            
        if (!$ultimoProtocolo) {
            $proximoNumero = 1;
        } else {
            // Extrair o número sequencial do último protocolo
            $partes = explode('-', $ultimoProtocolo->numero);
            $ultimoSequencial = (int) end($partes);
            $proximoNumero = $ultimoSequencial + 1;
        }
        
        return sprintf('PR-%d-%03d', $ano, $proximoNumero);
    }

    /**
     * Criar novo protocolo com número automático
     */
    public function criarProtocolo(array $dados): Protocolo
    {
        DB::beginTransaction();
        
        try {
            // Gerar número automático se não fornecido
            if (!isset($dados['numero']) || empty($dados['numero'])) {
                $dados['numero'] = $this->gerarNumeroProtocolo($dados['assembleia_id']);
            }
            
            // Definir status inicial se não fornecido
            if (!isset($dados['status'])) {
                $dados['status'] = 'rascunho';
            }
            
            // Definir prioridade padrão se não fornecido
            if (!isset($dados['prioridade'])) {
                $dados['prioridade'] = 'normal';
            }
            
            // Definir usuário criador
            if (!isset($dados['criado_por'])) {
                $dados['criado_por'] = Auth::id();
            }
            
            // Data de criação
            $dados['data_protocolo'] = $dados['data_protocolo'] ?? Carbon::now()->toDateString();
            
            $protocolo = Protocolo::create($dados);
            
            // Log da criação
            Log::info('Protocolo criado', [
                'protocolo_id' => $protocolo->id,
                'numero' => $protocolo->numero,
                'usuario_id' => Auth::id(),
            ]);
            
            DB::commit();
            return $protocolo;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao criar protocolo', [
                'erro' => $e->getMessage(),
                'dados' => $dados,
            ]);
            throw $e;
        }
    }

    /**
     * Alterar status do protocolo com validações
     */
    public function alterarStatus(Protocolo $protocolo, string $novoStatus, ?string $observacao = null): bool
    {
        $statusPermitidos = [
            'rascunho' => ['em_andamento', 'cancelado'],
            'em_andamento' => ['concluido', 'suspenso', 'cancelado'],
            'suspenso' => ['em_andamento', 'cancelado'],
            'concluido' => ['arquivado'],
            'cancelado' => [],
            'arquivado' => [],
        ];
        
        $statusAtual = $protocolo->status;
        
        // Verificar se a transição é permitida
        if (!isset($statusPermitidos[$statusAtual]) || 
            !in_array($novoStatus, $statusPermitidos[$statusAtual])) {
            throw new \Exception("Transição de status inválida: {$statusAtual} -> {$novoStatus}");
        }
        
        DB::beginTransaction();
        
        try {
            // Atualizar status
            $protocolo->update([
                'status' => $novoStatus,
                'data_conclusao' => $novoStatus === 'concluido' ? Carbon::now() : null,
            ]);
            
            // Registrar histórico se houver observação
            if ($observacao) {
                $protocolo->historico()->create([
                    'acao' => 'mudanca_status',
                    'status_anterior' => $statusAtual,
                    'status_novo' => $novoStatus,
                    'observacao' => $observacao,
                    'usuario_id' => Auth::id(),
                ]);
            }
            
            // Log da mudança
            Log::info('Status do protocolo alterado', [
                'protocolo_id' => $protocolo->id,
                'numero' => $protocolo->numero,
                'status_anterior' => $statusAtual,
                'status_novo' => $novoStatus,
                'usuario_id' => Auth::id(),
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao alterar status do protocolo', [
                'protocolo_id' => $protocolo->id,
                'erro' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verificar se protocolo pode ser editado
     */
    public function podeEditar(Protocolo $protocolo): bool
    {
        $statusEditaveis = ['rascunho', 'em_andamento', 'suspenso'];
        return in_array($protocolo->status, $statusEditaveis);
    }

    /**
     * Verificar se protocolo pode ser excluído
     */
    public function podeExcluir(Protocolo $protocolo): bool
    {
        return $protocolo->status === 'rascunho';
    }

    /**
     * Calcular prazo estimado baseado na prioridade
     */
    public function calcularPrazoEstimado(string $prioridade): Carbon
    {
        $diasPorPrioridade = [
            'baixa' => 30,
            'normal' => 15,
            'alta' => 7,
            'urgente' => 3,
        ];
        
        $dias = $diasPorPrioridade[$prioridade] ?? 15;
        return Carbon::now()->addDays($dias);
    }

    /**
     * Obter estatísticas de protocolos
     */
    public function obterEstatisticas(?int $assembleiaId = null): array
    {
        $query = Protocolo::query();
        
        if ($assembleiaId) {
            $query->where('assembleia_id', $assembleiaId);
        }
        
        $total = $query->count();
        $porStatus = $query->groupBy('status')
            ->selectRaw('status, count(*) as total')
            ->pluck('total', 'status')
            ->toArray();
            
        $porPrioridade = $query->groupBy('prioridade')
            ->selectRaw('prioridade, count(*) as total')
            ->pluck('total', 'prioridade')
            ->toArray();
            
        $atrasados = $query->where('prazo_estimado', '<', Carbon::now())
            ->whereNotIn('status', ['concluido', 'cancelado', 'arquivado'])
            ->count();
            
        $vencendoHoje = $query->whereDate('prazo_estimado', Carbon::today())
            ->whereNotIn('status', ['concluido', 'cancelado', 'arquivado'])
            ->count();
            
        return [
            'total' => $total,
            'por_status' => $porStatus,
            'por_prioridade' => $porPrioridade,
            'atrasados' => $atrasados,
            'vencendo_hoje' => $vencendoHoje,
            'em_dia' => $total - $atrasados,
        ];
    }

    /**
     * Validar dados do protocolo
     */
    public function validarProtocolo(array $dados): array
    {
        $erros = [];
        
        // Validar campos obrigatórios
        $obrigatorios = ['assembleia_id', 'assunto', 'descricao'];
        foreach ($obrigatorios as $campo) {
            if (!isset($dados[$campo]) || empty($dados[$campo])) {
                $erros[] = "Campo '{$campo}' é obrigatório";
            }
        }
        
        // Validar assembleia
        if (isset($dados['assembleia_id']) && !Assembleia::find($dados['assembleia_id'])) {
            $erros[] = 'Assembleia não encontrada';
        }
        
        // Validar status
        if (isset($dados['status'])) {
            $statusValidos = ['rascunho', 'em_andamento', 'suspenso', 'concluido', 'cancelado', 'arquivado'];
            if (!in_array($dados['status'], $statusValidos)) {
                $erros[] = 'Status inválido';
            }
        }
        
        // Validar prioridade
        if (isset($dados['prioridade'])) {
            $prioridadesValidas = ['baixa', 'normal', 'alta', 'urgente'];
            if (!in_array($dados['prioridade'], $prioridadesValidas)) {
                $erros[] = 'Prioridade inválida';
            }
        }
        
        return $erros;
    }
}