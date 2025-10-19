<?php

namespace App\Services\Actions;

use App\Models\Protocolo;
use App\Models\User;
use App\Models\ProtocoloHistorico;
use App\Services\MaioridadeService;
use Illuminate\Support\Facades\Log;

class MembroJurisdicaoAction implements ProtocoloActionInterface
{
    /**
     * Execute the action for membro jurisdicao steps
     */
    public function execute(Protocolo $protocolo, User $user, array $data = []): void
    {
        $etapaAtual = $protocolo->etapa_atual;
        
        switch ($etapaAtual) {
            case 'aprovacao':
            case 'aprovacao_final':
                $this->handleApproval($protocolo, $user, $data);
                break;
                
            case 'definir_taxas':
                $this->handleTaxDefinition($protocolo, $user, $data);
                break;
                
            default:
                $this->handleGenericAction($protocolo, $user, $data);
                break;
        }
        
        // Log the action
        $this->logAction($protocolo, $user, $etapaAtual, $data);
    }

    /**
     * Handle protocol approval
     */
    protected function handleApproval(Protocolo $protocolo, User $user, array $data): void
    {
        $aprovado = $data['aprovado'] ?? false;
        
        if ($aprovado) {
            $updateData = [
                'aprovado_por' => $user->id,
                'data_aprovacao' => now(),
                'status' => 'aprovado',
            ];

            // For protocols that require ceremony, set ceremony date
            if ($protocolo->requiresCeremony()) {
                if (isset($data['data_cerimonia'])) {
                    $updateData['data_cerimonia'] = $data['data_cerimonia'];
                } elseif ($protocolo->tipo_protocolo === 'maioridade') {
                    // For maioridade, ceremony date is required
                    throw new \InvalidArgumentException('Data da cerimônia é obrigatória para protocolos de maioridade');
                }
            }

            // Handle ceremony attendance for final approval
            if ($protocolo->etapa_atual === 'aprovacao_final' && isset($data['presenca_cerimonia'])) {
                $protocolo->definirPresencaCerimonia($data['presenca_cerimonia']);
            }

            $protocolo->update($updateData);

            // Execute post-approval actions based on protocol type
            $this->executePostApprovalActions($protocolo, $user, $data);
        } else {
            // Handle rejection
            $this->handleRejection($protocolo, $user, $data);
        }
    }

    /**
     * Handle tax definition
     */
    protected function handleTaxDefinition(Protocolo $protocolo, User $user, array $data): void
    {
        $updateData = [];

        if (isset($data['valor_taxa'])) {
            $updateData['valor_taxa'] = $data['valor_taxa'];
        }

        if (isset($data['observacoes_taxa'])) {
            $updateData['observacoes'] = ($protocolo->observacoes ?? '') . "\n\nTaxas definidas: " . $data['observacoes_taxa'];
        }

        if (!empty($updateData)) {
            $protocolo->update($updateData);
        }
    }

    /**
     * Handle protocol rejection
     */
    protected function handleRejection(Protocolo $protocolo, User $user, array $data): void
    {
        $protocolo->update([
            'status' => 'rejeitado',
            'feedback_rejeicao' => $data['feedback_rejeicao'] ?? 'Protocolo rejeitado sem feedback específico',
            'aprovado_por' => $user->id,
            'data_aprovacao' => now(),
        ]);
    }

    /**
     * Execute post-approval actions based on protocol type
     */
    protected function executePostApprovalActions(Protocolo $protocolo, User $user, array $data): void
    {
        switch ($protocolo->tipo_protocolo) {
            case 'iniciacao':
                $this->handleIniciacaoApproval($protocolo, $user, $data);
                break;
                
            case 'maioridade':
                $this->handleMaioridadeApproval($protocolo, $user, $data);
                break;
                
            case 'afastamento':
                $this->handleAfastamentoApproval($protocolo, $user, $data);
                break;
                
            case 'novos_cargos_assembleia':
                $this->handleCargosAssembleiaApproval($protocolo, $user, $data);
                break;
                
            case 'novos_cargos_conselho':
                $this->handleCargosConselhoApproval($protocolo, $user, $data);
                break;
                
            case 'homenageados_ano':
            case 'coracao_cores':
            case 'grande_cruz_cores':
                $this->handleHonrariasApproval($protocolo, $user, $data);
                break;
        }
    }

    /**
     * Handle iniciacao protocol approval
     */
    protected function handleIniciacaoApproval(Protocolo $protocolo, User $user, array $data): void
    {
        // Create new member profiles and send welcome emails
        $iniciacaoService = new \App\Services\IniciacaoService();
        
        try {
            $results = $iniciacaoService->processProtocolCompletion($protocolo);
            $summary = $iniciacaoService->getProcessingSummary($results);
            
            Log::info('Iniciacao protocol approved - members created', [
                'protocolo_id' => $protocolo->id,
                'total_members' => $summary['total'],
                'successful' => $summary['successful'],
                'failed' => $summary['failed'],
            ]);
            
            // If there were failures, log them
            if ($summary['failed'] > 0) {
                $failures = array_filter($results, fn($r) => !$r['success']);
                Log::warning('Some members failed to be created during iniciacao approval', [
                    'protocolo_id' => $protocolo->id,
                    'failures' => $failures,
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to process iniciacao protocol approval', [
                'protocolo_id' => $protocolo->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle maioridade protocol approval
     */
    protected function handleMaioridadeApproval(Protocolo $protocolo, User $user, array $data): void
    {
        $maioridadeService = new MaioridadeService();
        
        // Validate protocol data
        $errors = $maioridadeService->validateProtocolData($protocolo);
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Erro na validação do protocolo: ' . implode(', ', $errors));
        }
        
        // Process maioridade ceremony
        $maioridadeService->processMaioridadeCeremony($protocolo, $protocolo->data_cerimonia);
        
        Log::info('Maioridade protocol approved - member status updated', [
            'protocolo_id' => $protocolo->id,
            'members_updated' => $protocolo->membros->count(),
            'ceremony_date' => $protocolo->data_cerimonia,
        ]);
    }

    /**
     * Handle afastamento protocol approval
     */
    protected function handleAfastamentoApproval(Protocolo $protocolo, User $user, array $data): void
    {
        // Update member status to inactive
        foreach ($protocolo->membros as $membro) {
            $membro->update([
                'ativo' => false,
                'data_afastamento' => $data['data_afastamento'] ?? now(),
            ]);
        }
    }

    /**
     * Handle cargos assembleia protocol approval
     */
    protected function handleCargosAssembleiaApproval(Protocolo $protocolo, User $user, array $data): void
    {
        // Update assembleia positions
        // This would use the CargoAssembleiaService
        Log::info('Cargos Assembleia protocol approved - position updates pending', [
            'protocolo_id' => $protocolo->id,
        ]);
    }

    /**
     * Handle cargos conselho protocol approval
     */
    protected function handleCargosConselhoApproval(Protocolo $protocolo, User $user, array $data): void
    {
        // Update conselho positions
        // This would use the CargoConselhoService
        Log::info('Cargos Conselho protocol approved - position updates pending', [
            'protocolo_id' => $protocolo->id,
        ]);
    }

    /**
     * Handle honrarias protocol approval
     */
    protected function handleHonrariasApproval(Protocolo $protocolo, User $user, array $data): void
    {
        // Update member honors
        foreach ($protocolo->membros as $membro) {
            // Create honor record
            $membro->honrarias()->create([
                'tipo' => $protocolo->tipo_protocolo,
                'data_recebimento' => $protocolo->data_cerimonia ?? now(),
                'protocolo_id' => $protocolo->id,
                'presente_cerimonia' => $membro->pivot->presente_cerimonia ?? true,
            ]);
        }
    }

    /**
     * Handle generic jurisdiction member actions
     */
    protected function handleGenericAction(Protocolo $protocolo, User $user, array $data): void
    {
        // Handle general updates that jurisdiction members can make
        $allowedFields = [
            'observacoes',
            'valor_taxa',
            'data_cerimonia',
        ];

        $updateData = [];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $protocolo->update($updateData);
        }
    }

    /**
     * Log the action performed
     */
    protected function logAction(Protocolo $protocolo, User $user, string $etapa, array $data): void
    {
        $descricao = $this->getActionDescription($etapa, $data);
        
        ProtocoloHistorico::create([
            'protocolo_id' => $protocolo->id,
            'user_id' => $user->id,
            'tipo' => 'acao_jurisdicao',
            'descricao' => $descricao,
            'dados_anteriores' => $protocolo->getOriginal(),
            'dados_novos' => $protocolo->getAttributes(),
            'observacoes' => $data['observacoes_log'] ?? null,
        ]);

        Log::info('Membro Jurisdicao Action executed', [
            'protocolo_id' => $protocolo->id,
            'user_id' => $user->id,
            'etapa' => $etapa,
            'action' => $descricao,
        ]);
    }

    /**
     * Get description for the action performed
     */
    protected function getActionDescription(string $etapa, array $data): string
    {
        return match($etapa) {
            'aprovacao' => isset($data['aprovado']) && $data['aprovado'] 
                ? 'Protocolo aprovado pela Jurisdição'
                : 'Protocolo rejeitado pela Jurisdição',
            'aprovacao_final' => isset($data['aprovado']) && $data['aprovado']
                ? 'Protocolo aprovado na etapa final pela Jurisdição'
                : 'Protocolo rejeitado na etapa final pela Jurisdição',
            'definir_taxas' => 'Taxas definidas pela Jurisdição',
            default => 'Ação executada pela Jurisdição',
        };
    }

    /**
     * Validate if user can perform this action
     */
    public function canExecute(User $user, Protocolo $protocolo): bool
    {
        return $user->hasRole('membro_jurisdicao');
    }

    /**
     * Get required data fields for this action
     */
    public function getRequiredFields(string $etapa): array
    {
        return match($etapa) {
            'aprovacao', 'aprovacao_final' => ['aprovado'],
            'definir_taxas' => ['valor_taxa'],
            default => [],
        };
    }

    /**
     * Get optional data fields for this action
     */
    public function getOptionalFields(string $etapa): array
    {
        return match($etapa) {
            'aprovacao', 'aprovacao_final' => ['feedback_rejeicao', 'data_cerimonia', 'presenca_cerimonia', 'observacoes'],
            'definir_taxas' => ['observacoes_taxa'],
            default => ['observacoes'],
        };
    }
}