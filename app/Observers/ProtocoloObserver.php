<?php

namespace App\Observers;

use App\Models\Protocolo;
use App\Services\IniciacaoService;
use App\Helpers\ProtocoloLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProtocoloObserver
{
    /**
     * Handle the Protocolo "creating" event.
     * Executado antes de criar o protocolo
     */
    public function creating(Protocolo $protocolo): void
    {
        // Inicializar workflow se não definido
        if (!$protocolo->etapa_atual) {
            $workflow = $protocolo->getWorkflow();
            $protocolo->etapa_atual = $workflow->getInitialStep();
        }

        // Definir status inicial se não definido
        if (!$protocolo->status) {
            $protocolo->status = 'rascunho';
        }

        // Definir solicitante se não definido
        if (!$protocolo->solicitante_id && Auth::check()) {
            $protocolo->solicitante_id = Auth::id();
        }

        // Definir data de solicitação
        if (!$protocolo->data_solicitacao) {
            $protocolo->data_solicitacao = now();
        }
    }

    /**
     * Handle the Protocolo "created" event.
     * Executado após criar o protocolo
     */
    public function created(Protocolo $protocolo): void
    {
        // Usar ProtocoloLogger para criar log padronizado
        ProtocoloLogger::logCriacao($protocolo);

        // Inicializar configuração de etapas
        if (!$protocolo->configuracao_etapas) {
            $workflow = $protocolo->getWorkflow();
            $protocolo->update([
                'configuracao_etapas' => $workflow->getAllSteps(),
            ]);
        }
    }

    /**
     * Handle the Protocolo "updated" event.
     */
    public function updated(Protocolo $protocolo): void
    {
        $changes = $protocolo->getChanges();
        $original = $protocolo->getOriginal();
        
        // Log general changes
        if (!empty($changes)) {
            $this->logGeneralChanges($protocolo, $changes, $original);
        }
        
        // Check if the protocol was just completed and is an iniciacao protocol
        if ($protocolo->isDirty('etapa_atual') && 
            $protocolo->etapa_atual === 'concluido' && 
            $protocolo->tipo_protocolo === 'iniciacao') {
            
            $this->processIniciacaoCompletion($protocolo);
        }
    }

    /**
     * Process iniciacao protocol completion
     */
    protected function processIniciacaoCompletion(Protocolo $protocolo): void
    {
        try {
            $iniciacaoService = new IniciacaoService();
            
            // Validate all member data before processing
            $novasMeninas = $protocolo->dados_membros ?? [];
            $validationErrors = [];
            
            foreach ($novasMeninas as $index => $dadosMenina) {
                $errors = $iniciacaoService->validateMemberData($dadosMenina);
                if (!empty($errors)) {
                    $validationErrors[$index] = $errors;
                }
            }
            
            // If there are validation errors, log them and don't process
            if (!empty($validationErrors)) {
                Log::warning('Iniciação protocol has validation errors', [
                    'protocol_id' => $protocolo->id,
                    'errors' => $validationErrors
                ]);
                
                // Add a comment to the protocol about validation issues
                $protocolo->historico()->create([
                    'user_id' => $protocolo->aprovado_por,
                    'acao' => 'validacao_erro',
                    'descricao' => 'Erros de validação encontrados durante processamento automático',
                    'comentario' => 'Erros encontrados: ' . json_encode($validationErrors, JSON_UNESCAPED_UNICODE),
                    'dados_anteriores' => null,
                    'dados_novos' => ['validation_errors' => $validationErrors],
                    'created_at' => now(),
                ]);
                
                return;
            }
            
            // Process the completion
            $results = $iniciacaoService->processProtocolCompletion($protocolo);
            
            Log::info('Iniciação protocol processed automatically', [
                'protocol_id' => $protocolo->id,
                'results_summary' => $iniciacaoService->getProcessingSummary($results)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to process iniciacao protocol completion', [
                'protocol_id' => $protocolo->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Add error log to protocol history
            $protocolo->historico()->create([
                'user_id' => $protocolo->aprovado_por,
                'acao' => 'processamento_erro',
                'descricao' => 'Erro durante processamento automático',
                'comentario' => 'Erro: ' . $e->getMessage(),
                'dados_anteriores' => null,
                'dados_novos' => ['error' => $e->getMessage()],
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Log general changes to the protocol
     */
    protected function logGeneralChanges(Protocolo $protocolo, array $changes, array $original): void
    {
        $significantChanges = array_intersect_key($changes, array_flip([
            'status', 'etapa_atual', 'aprovado_por', 'data_aprovacao', 'feedback_rejeicao',
            'valor_taxa', 'data_cerimonia', 'dados_membros'
        ]));

        if (empty($significantChanges)) {
            return;
        }

        $acao = 'edicao';
        $descricao = 'Protocolo atualizado';
        
        // Determine specific action based on changes
        if (isset($changes['etapa_atual'])) {
            $acao = 'transicao_etapa';
            $etapaAnterior = $original['etapa_atual'] ?? 'nenhuma';
            $descricao = "Etapa alterada de '{$etapaAnterior}' para '{$changes['etapa_atual']}'";

            // Set data_conclusao automatically when protocol is completed
            if ($changes['etapa_atual'] === 'concluido' && !$protocolo->data_conclusao) {
                $protocolo->update(['data_conclusao' => now()]);
            }
        } elseif (isset($changes['status'])) {
            $acao = 'mudanca_status';
            $statusAnterior = $original['status'] ?? 'nenhum';
            $descricao = "Status alterado de '{$statusAnterior}' para '{$changes['status']}'";

            // Set data_conclusao automatically when status is completed
            if ($changes['status'] === 'concluido' && !$protocolo->data_conclusao) {
                $protocolo->update(['data_conclusao' => now()]);
            }
        } elseif (isset($changes['aprovado_por'])) {
            $acao = $protocolo->status === 'concluido' ? 'aprovacao' : 'rejeicao';
            $descricao = $protocolo->status === 'concluido' ? 'Protocolo aprovado' : 'Protocolo rejeitado';
        }

        $protocolo->historico()->create([
            'user_id' => Auth::id() ?? $protocolo->solicitante_id,
            'acao' => $acao,
            'descricao' => $descricao,
            'comentario' => $this->buildChangeComment($changes, $original),
            'status_anterior' => $original['status'] ?? null,
            'status_novo' => $protocolo->status,
            'etapa_anterior' => $original['etapa_atual'] ?? null,
            'etapa_nova' => $protocolo->etapa_atual,
            'dados_anteriores' => array_intersect_key($original, $significantChanges),
            'dados_novos' => $significantChanges,
            'created_at' => now(),
        ]);
    }

    /**
     * Build a human-readable comment about changes
     */
    protected function buildChangeComment(array $changes, array $original): string
    {
        $comments = [];

        foreach ($changes as $field => $newValue) {
            $oldValue = $original[$field] ?? null;
            
            switch ($field) {
                case 'status':
                    $comments[] = "Status: {$oldValue} → {$newValue}";
                    break;
                case 'etapa_atual':
                    $comments[] = "Etapa: {$oldValue} → {$newValue}";
                    break;
                case 'aprovado_por':
                    $user = \App\Models\User::find($newValue);
                    $comments[] = "Aprovado por: " . ($user ? $user->name : 'ID ' . $newValue);
                    break;
                case 'data_aprovacao':
                    $comments[] = "Data de aprovação: " . \Carbon\Carbon::parse($newValue)->format('d/m/Y H:i');
                    break;
                case 'feedback_rejeicao':
                    $comments[] = "Feedback de rejeição adicionado";
                    break;
                case 'valor_taxa':
                    $comments[] = "Taxa: R$ " . number_format($oldValue ?? 0, 2, ',', '.') . " → R$ " . number_format($newValue, 2, ',', '.');
                    break;
                case 'data_cerimonia':
                    $comments[] = "Data da cerimônia: " . \Carbon\Carbon::parse($newValue)->format('d/m/Y');
                    break;
                case 'dados_membros':
                    $oldCount = is_array($oldValue) ? count($oldValue) : 0;
                    $newCount = is_array($newValue) ? count($newValue) : 0;
                    $comments[] = "Membros: {$oldCount} → {$newCount}";
                    break;
            }
        }

        return implode('; ', $comments);
    }
}