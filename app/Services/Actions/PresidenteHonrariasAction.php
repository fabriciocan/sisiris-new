<?php

namespace App\Services\Actions;

use App\Models\Protocolo;
use App\Models\User;
use App\Models\ProtocoloHistorico;
use Illuminate\Support\Facades\Log;

class PresidenteHonrariasAction implements ProtocoloActionInterface
{
    /**
     * Execute the action for presidente honrarias steps
     */
    public function execute(Protocolo $protocolo, User $user, array $data = []): void
    {
        $etapaAtual = $protocolo->etapa_atual;
        
        switch ($etapaAtual) {
            case 'aprovacao_honrarias':
                $this->handleHonorsApproval($protocolo, $user, $data);
                break;
                
            default:
                $this->handleGenericAction($protocolo, $user, $data);
                break;
        }
        
        // Log the action
        $this->logAction($protocolo, $user, $etapaAtual, $data);
    }

    /**
     * Handle honors committee approval
     */
    protected function handleHonorsApproval(Protocolo $protocolo, User $user, array $data): void
    {
        $aprovado = $data['aprovado'] ?? false;
        
        if ($aprovado) {
            $updateData = [
                'status' => 'aprovado_honrarias',
            ];

            // Add honors committee approval notes
            if (isset($data['observacoes_aprovacao'])) {
                $observacoes = $protocolo->observacoes ?? '';
                $observacoes .= "\n\nAprovação Comissão de Honrarias: " . $data['observacoes_aprovacao'];
                $updateData['observacoes'] = $observacoes;
            }

            // Handle member eligibility validation
            if (isset($data['membros_validados'])) {
                $this->validateMemberEligibility($protocolo, $data['membros_validados']);
            }

            $protocolo->update($updateData);

            // Execute honors-specific validations
            $this->executeHonorsValidation($protocolo, $user, $data);
        } else {
            // Handle rejection
            $this->handleRejection($protocolo, $user, $data);
        }
    }

    /**
     * Handle protocol rejection by honors committee
     */
    protected function handleRejection(Protocolo $protocolo, User $user, array $data): void
    {
        $protocolo->update([
            'status' => 'rejeitado_honrarias',
            'feedback_rejeicao' => $data['feedback_rejeicao'] ?? 'Protocolo rejeitado pela Comissão de Honrarias sem feedback específico',
        ]);
    }

    /**
     * Validate member eligibility for honors
     */
    protected function validateMemberEligibility(Protocolo $protocolo, array $membrosValidados): void
    {
        foreach ($protocolo->membros as $membro) {
            $isValid = in_array($membro->id, $membrosValidados);
            
            if (!$isValid) {
                // Remove ineligible member from protocol
                $protocolo->removeMembro($membro);
                
                Log::info('Member removed from honors protocol due to ineligibility', [
                    'protocolo_id' => $protocolo->id,
                    'membro_id' => $membro->id,
                    'membro_nome' => $membro->nome,
                ]);
            }
        }
    }

    /**
     * Execute honors-specific validation
     */
    protected function executeHonorsValidation(Protocolo $protocolo, User $user, array $data): void
    {
        switch ($protocolo->tipo_protocolo) {
            case 'homenageados_ano':
                $this->validateHomenageadosAno($protocolo, $data);
                break;
                
            case 'coracao_cores':
                $this->validateCoracaoCores($protocolo, $data);
                break;
                
            case 'grande_cruz_cores':
                $this->validateGrandeCruzCores($protocolo, $data);
                break;
        }
    }

    /**
     * Validate Homenageados do Ano protocol
     */
    protected function validateHomenageadosAno(Protocolo $protocolo, array $data): void
    {
        // Check if members are eligible for annual honors
        foreach ($protocolo->membros as $membro) {
            // Validate member is active
            if (!$membro->ativo) {
                Log::warning('Inactive member in Homenageados do Ano protocol', [
                    'protocolo_id' => $protocolo->id,
                    'membro_id' => $membro->id,
                ]);
            }

            // Check if member already received this honor this year
            $currentYear = now()->year;
            $existingHonor = $membro->honrarias()
                ->where('tipo', 'homenageados_ano')
                ->whereYear('data_recebimento', $currentYear)
                ->exists();

            if ($existingHonor) {
                Log::warning('Member already received Homenageados do Ano this year', [
                    'protocolo_id' => $protocolo->id,
                    'membro_id' => $membro->id,
                    'year' => $currentYear,
                ]);
            }
        }
    }

    /**
     * Validate Coração das Cores protocol
     */
    protected function validateCoracaoCores(Protocolo $protocolo, array $data): void
    {
        // Check if members already have this honor
        foreach ($protocolo->membros as $membro) {
            $existingHonor = $membro->honrarias()
                ->where('tipo', 'coracao_cores')
                ->exists();

            if ($existingHonor) {
                // Remove member as they already have this honor
                $protocolo->removeMembro($membro);
                
                Log::info('Member removed from Coração das Cores protocol - already has honor', [
                    'protocolo_id' => $protocolo->id,
                    'membro_id' => $membro->id,
                ]);
            }
        }
    }

    /**
     * Validate Grande Cruz das Cores protocol
     */
    protected function validateGrandeCruzCores(Protocolo $protocolo, array $data): void
    {
        // Check if members already have this honor
        foreach ($protocolo->membros as $membro) {
            $existingHonor = $membro->honrarias()
                ->where('tipo', 'grande_cruz_cores')
                ->exists();

            if ($existingHonor) {
                // Remove member as they already have this honor
                $protocolo->removeMembro($membro);
                
                Log::info('Member removed from Grande Cruz das Cores protocol - already has honor', [
                    'protocolo_id' => $protocolo->id,
                    'membro_id' => $membro->id,
                ]);
            }

            // Validate member has prerequisite honors (if required)
            $hasCoracaoCores = $membro->honrarias()
                ->where('tipo', 'coracao_cores')
                ->exists();

            if (!$hasCoracaoCores && isset($data['require_prerequisite']) && $data['require_prerequisite']) {
                Log::warning('Member does not have prerequisite Coração das Cores honor', [
                    'protocolo_id' => $protocolo->id,
                    'membro_id' => $membro->id,
                ]);
            }
        }
    }

    /**
     * Handle generic honors committee actions
     */
    protected function handleGenericAction(Protocolo $protocolo, User $user, array $data): void
    {
        // Handle general updates that honors committee can make
        $allowedFields = [
            'observacoes',
        ];

        $updateData = [];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $currentValue = $protocolo->$field ?? '';
                $updateData[$field] = $currentValue . "\n\nComissão de Honrarias: " . $data[$field];
            }
        }

        if (!empty($updateData)) {
            $protocolo->update($updateData);
        }

        // Handle member validation
        if (isset($data['validar_membros']) && $data['validar_membros']) {
            $this->executeHonorsValidation($protocolo, $user, $data);
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
            'tipo' => 'acao_honrarias',
            'descricao' => $descricao,
            'dados_anteriores' => $protocolo->getOriginal(),
            'dados_novos' => $protocolo->getAttributes(),
            'observacoes' => $data['observacoes_log'] ?? null,
        ]);

        Log::info('Presidente Honrarias Action executed', [
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
            'aprovacao_honrarias' => isset($data['aprovado']) && $data['aprovado']
                ? 'Protocolo aprovado pela Comissão de Honrarias'
                : 'Protocolo rejeitado pela Comissão de Honrarias',
            default => 'Ação executada pela Comissão de Honrarias',
        };
    }

    /**
     * Validate if user can perform this action
     */
    public function canExecute(User $user, Protocolo $protocolo): bool
    {
        return $user->hasRole('presidente_honrarias');
    }

    /**
     * Get required data fields for this action
     */
    public function getRequiredFields(string $etapa): array
    {
        return match($etapa) {
            'aprovacao_honrarias' => ['aprovado'],
            default => [],
        };
    }

    /**
     * Get optional data fields for this action
     */
    public function getOptionalFields(string $etapa): array
    {
        return match($etapa) {
            'aprovacao_honrarias' => [
                'feedback_rejeicao', 
                'observacoes_aprovacao', 
                'membros_validados',
                'require_prerequisite'
            ],
            default => ['observacoes', 'validar_membros'],
        };
    }

    /**
     * Get honors-specific validation rules
     */
    public function getHonorsValidationRules(string $tipoProtocolo): array
    {
        return match($tipoProtocolo) {
            'homenageados_ano' => [
                'member_must_be_active' => true,
                'check_annual_limit' => true,
                'allow_multiple_per_year' => false,
            ],
            'coracao_cores' => [
                'member_must_be_active' => true,
                'check_existing_honor' => true,
                'unique_honor' => true,
            ],
            'grande_cruz_cores' => [
                'member_must_be_active' => true,
                'check_existing_honor' => true,
                'unique_honor' => true,
                'prerequisite_honors' => ['coracao_cores'],
            ],
            default => [],
        };
    }
}