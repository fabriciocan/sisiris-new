<?php

namespace App\Services;

use App\Models\Protocolo;
use App\Models\User;
use App\Services\Actions\AdminAssembleiaAction;
use App\Services\Actions\MembroJurisdicaoAction;
use App\Services\Actions\PresidenteHonrariasAction;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ProtocoloWorkflow
{
    protected Protocolo $protocolo;
    
    /**
     * Workflow configurations for each protocol type
     */
    protected array $workflows = [
        'maioridade' => [
            'steps' => [
                'criacao' => [
                    'name' => 'Criação',
                    'action' => AdminAssembleiaAction::class,
                    'next_steps' => ['aguardando_aprovacao'],
                    'required_roles' => ['admin_assembleia'],
                ],
                'aguardando_aprovacao' => [
                    'name' => 'Aguardando Aprovação',
                    'action' => null,
                    'next_steps' => ['aprovacao'],
                    'required_roles' => [],
                ],
                'aprovacao' => [
                    'name' => 'Aprovação',
                    'action' => MembroJurisdicaoAction::class,
                    'next_steps' => ['concluido', 'rejeitado'],
                    'required_roles' => ['membro_jurisdicao'],
                ],
                'concluido' => [
                    'name' => 'Concluído',
                    'action' => null,
                    'next_steps' => [],
                    'required_roles' => [],
                ],
                'rejeitado' => [
                    'name' => 'Rejeitado',
                    'action' => null,
                    'next_steps' => ['criacao'],
                    'required_roles' => [],
                ],
            ],
            'initial_step' => 'criacao',
        ],
        
        'iniciacao' => [
            'steps' => [
                'criacao' => [
                    'name' => 'Criação',
                    'action' => AdminAssembleiaAction::class,
                    'next_steps' => ['aprovacao'],
                    'required_roles' => ['admin_assembleia'],
                ],
                'aprovacao' => [
                    'name' => 'Aprovação',
                    'action' => MembroJurisdicaoAction::class,
                    'next_steps' => ['concluido', 'rejeitado'],
                    'required_roles' => ['membro_jurisdicao'],
                ],
                'concluido' => [
                    'name' => 'Concluído',
                    'action' => null,
                    'next_steps' => [],
                    'required_roles' => [],
                ],
                'rejeitado' => [
                    'name' => 'Rejeitado',
                    'action' => null,
                    'next_steps' => ['criacao'],
                    'required_roles' => [],
                ],
            ],
            'initial_step' => 'criacao',
        ],
        
        'homenageados_ano' => [
            'steps' => [
                'criacao' => [
                    'name' => 'Criação',
                    'action' => AdminAssembleiaAction::class,
                    'next_steps' => ['aprovacao_honrarias'],
                    'required_roles' => ['admin_assembleia'],
                ],
                'aprovacao_honrarias' => [
                    'name' => 'Aprovação Comissão de Honrarias',
                    'action' => PresidenteHonrariasAction::class,
                    'next_steps' => ['definir_taxas', 'rejeitado'],
                    'required_roles' => ['presidente_honrarias'],
                ],
                'definir_taxas' => [
                    'name' => 'Definir Taxas',
                    'action' => MembroJurisdicaoAction::class,
                    'next_steps' => ['aguardando_pagamento'],
                    'required_roles' => ['membro_jurisdicao'],
                ],
                'aguardando_pagamento' => [
                    'name' => 'Aguardando Pagamento',
                    'action' => AdminAssembleiaAction::class,
                    'next_steps' => ['aprovacao_final'],
                    'required_roles' => ['admin_assembleia'],
                ],
                'aprovacao_final' => [
                    'name' => 'Aprovação Final',
                    'action' => MembroJurisdicaoAction::class,
                    'next_steps' => ['concluido', 'rejeitado'],
                    'required_roles' => ['membro_jurisdicao'],
                ],
                'concluido' => [
                    'name' => 'Concluído',
                    'action' => null,
                    'next_steps' => [],
                    'required_roles' => [],
                ],
                'rejeitado' => [
                    'name' => 'Rejeitado',
                    'action' => null,
                    'next_steps' => ['criacao'],
                    'required_roles' => [],
                ],
            ],
            'initial_step' => 'criacao',
        ],
        
        'coracao_cores' => [
            'steps' => [
                'criacao' => [
                    'name' => 'Criação',
                    'action' => AdminAssembleiaAction::class,
                    'next_steps' => ['aprovacao_honrarias'],
                    'required_roles' => ['admin_assembleia'],
                ],
                'aprovacao_honrarias' => [
                    'name' => 'Aprovação Comissão de Honrarias',
                    'action' => PresidenteHonrariasAction::class,
                    'next_steps' => ['definir_taxas', 'rejeitado'],
                    'required_roles' => ['presidente_honrarias'],
                ],
                'definir_taxas' => [
                    'name' => 'Definir Taxas',
                    'action' => MembroJurisdicaoAction::class,
                    'next_steps' => ['aguardando_pagamento'],
                    'required_roles' => ['membro_jurisdicao'],
                ],
                'aguardando_pagamento' => [
                    'name' => 'Aguardando Pagamento',
                    'action' => AdminAssembleiaAction::class,
                    'next_steps' => ['aprovacao_final'],
                    'required_roles' => ['admin_assembleia'],
                ],
                'aprovacao_final' => [
                    'name' => 'Aprovação Final',
                    'action' => MembroJurisdicaoAction::class,
                    'next_steps' => ['concluido', 'rejeitado'],
                    'required_roles' => ['membro_jurisdicao'],
                ],
                'concluido' => [
                    'name' => 'Concluído',
                    'action' => null,
                    'next_steps' => [],
                    'required_roles' => [],
                ],
                'rejeitado' => [
                    'name' => 'Rejeitado',
                    'action' => null,
                    'next_steps' => ['criacao'],
                    'required_roles' => [],
                ],
            ],
            'initial_step' => 'criacao',
        ],
        
        'grande_cruz_cores' => [
            'steps' => [
                'criacao' => [
                    'name' => 'Criação',
                    'action' => AdminAssembleiaAction::class,
                    'next_steps' => ['aprovacao_honrarias'],
                    'required_roles' => ['admin_assembleia'],
                ],
                'aprovacao_honrarias' => [
                    'name' => 'Aprovação Comissão de Honrarias',
                    'action' => PresidenteHonrariasAction::class,
                    'next_steps' => ['definir_taxas', 'rejeitado'],
                    'required_roles' => ['presidente_honrarias'],
                ],
                'definir_taxas' => [
                    'name' => 'Definir Taxas',
                    'action' => MembroJurisdicaoAction::class,
                    'next_steps' => ['aguardando_pagamento'],
                    'required_roles' => ['membro_jurisdicao'],
                ],
                'aguardando_pagamento' => [
                    'name' => 'Aguardando Pagamento',
                    'action' => AdminAssembleiaAction::class,
                    'next_steps' => ['aprovacao_final'],
                    'required_roles' => ['admin_assembleia'],
                ],
                'aprovacao_final' => [
                    'name' => 'Aprovação Final',
                    'action' => MembroJurisdicaoAction::class,
                    'next_steps' => ['concluido', 'rejeitado'],
                    'required_roles' => ['membro_jurisdicao'],
                ],
                'concluido' => [
                    'name' => 'Concluído',
                    'action' => null,
                    'next_steps' => [],
                    'required_roles' => [],
                ],
                'rejeitado' => [
                    'name' => 'Rejeitado',
                    'action' => null,
                    'next_steps' => ['criacao'],
                    'required_roles' => [],
                ],
            ],
            'initial_step' => 'criacao',
        ],
        
        'afastamento' => [
            'steps' => [
                'criacao' => [
                    'name' => 'Criação',
                    'action' => AdminAssembleiaAction::class,
                    'next_steps' => ['aprovacao'],
                    'required_roles' => ['admin_assembleia'],
                ],
                'aprovacao' => [
                    'name' => 'Aprovação',
                    'action' => MembroJurisdicaoAction::class,
                    'next_steps' => ['concluido', 'rejeitado'],
                    'required_roles' => ['membro_jurisdicao'],
                ],
                'concluido' => [
                    'name' => 'Concluído',
                    'action' => null,
                    'next_steps' => [],
                    'required_roles' => [],
                ],
                'rejeitado' => [
                    'name' => 'Rejeitado',
                    'action' => null,
                    'next_steps' => ['criacao'],
                    'required_roles' => [],
                ],
            ],
            'initial_step' => 'criacao',
        ],
        
        'novos_cargos_assembleia' => [
            'steps' => [
                'criacao' => [
                    'name' => 'Criação',
                    'action' => AdminAssembleiaAction::class,
                    'next_steps' => ['aprovacao'],
                    'required_roles' => ['admin_assembleia'],
                ],
                'aprovacao' => [
                    'name' => 'Aprovação',
                    'action' => MembroJurisdicaoAction::class,
                    'next_steps' => ['concluido', 'rejeitado'],
                    'required_roles' => ['membro_jurisdicao'],
                ],
                'concluido' => [
                    'name' => 'Concluído',
                    'action' => null,
                    'next_steps' => [],
                    'required_roles' => [],
                ],
                'rejeitado' => [
                    'name' => 'Rejeitado',
                    'action' => null,
                    'next_steps' => ['criacao'],
                    'required_roles' => [],
                ],
            ],
            'initial_step' => 'criacao',
        ],
        
        'novos_cargos_conselho' => [
            'steps' => [
                'criacao' => [
                    'name' => 'Criação',
                    'action' => AdminAssembleiaAction::class,
                    'next_steps' => ['aprovacao'],
                    'required_roles' => ['admin_assembleia'],
                ],
                'aprovacao' => [
                    'name' => 'Aprovação',
                    'action' => MembroJurisdicaoAction::class,
                    'next_steps' => ['concluido', 'rejeitado'],
                    'required_roles' => ['membro_jurisdicao'],
                ],
                'concluido' => [
                    'name' => 'Concluído',
                    'action' => null,
                    'next_steps' => [],
                    'required_roles' => [],
                ],
                'rejeitado' => [
                    'name' => 'Rejeitado',
                    'action' => null,
                    'next_steps' => ['criacao'],
                    'required_roles' => [],
                ],
            ],
            'initial_step' => 'criacao',
        ],
    ];

    public function __construct(Protocolo $protocolo)
    {
        $this->protocolo = $protocolo;
    }

    /**
     * Get the workflow configuration for the protocol type
     */
    public function getWorkflowConfig(): array
    {
        $tipo = $this->protocolo->tipo_protocolo ?? $this->protocolo->tipo;
        
        if (!isset($this->workflows[$tipo])) {
            throw new InvalidArgumentException("Workflow não definido para o tipo de protocolo: {$tipo}");
        }

        return $this->workflows[$tipo];
    }

    /**
     * Get the current step configuration
     */
    public function getCurrentStep(): array
    {
        $workflow = $this->getWorkflowConfig();
        $currentStep = $this->protocolo->etapa_atual ?? $workflow['initial_step'];
        
        if (!isset($workflow['steps'][$currentStep])) {
            throw new InvalidArgumentException("Etapa atual inválida: {$currentStep}");
        }

        return $workflow['steps'][$currentStep];
    }

    /**
     * Get possible next steps from current step
     */
    public function getNextSteps(): array
    {
        $currentStep = $this->getCurrentStep();
        $workflow = $this->getWorkflowConfig();
        
        $nextSteps = [];
        foreach ($currentStep['next_steps'] as $stepKey) {
            if (isset($workflow['steps'][$stepKey])) {
                $nextSteps[$stepKey] = $workflow['steps'][$stepKey];
            }
        }
        
        return $nextSteps;
    }

    /**
     * Check if a transition to a specific step is valid
     */
    public function canTransitionTo(string $targetStep): bool
    {
        $currentStep = $this->getCurrentStep();
        return in_array($targetStep, $currentStep['next_steps']);
    }

    /**
     * Check if user can perform action on current step
     */
    public function canUserPerformAction(User $user): bool
    {
        $currentStep = $this->getCurrentStep();
        
        if (empty($currentStep['required_roles'])) {
            return true;
        }
        
        foreach ($currentStep['required_roles'] as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Transition to next step
     */
    public function transitionTo(string $targetStep, User $user, array $data = []): bool
    {
        if (!$this->canTransitionTo($targetStep)) {
            throw new InvalidArgumentException("Transição inválida para a etapa: {$targetStep}");
        }

        if (!$this->canUserPerformAction($user)) {
            throw new InvalidArgumentException("Usuário não tem permissão para executar esta ação");
        }

        // Update protocol step
        $this->protocolo->update([
            'etapa_atual' => $targetStep,
            'status' => $this->mapStepToStatus($targetStep),
        ]);

        // Execute step action if defined
        $workflow = $this->getWorkflowConfig();
        $stepConfig = $workflow['steps'][$targetStep];
        
        if ($stepConfig['action']) {
            $actionClass = $stepConfig['action'];
            $action = new $actionClass();
            $action->execute($this->protocolo, $user, $data);
        }

        return true;
    }

    /**
     * Get all available steps for the protocol type
     */
    public function getAllSteps(): array
    {
        $workflow = $this->getWorkflowConfig();
        return $workflow['steps'];
    }

    /**
     * Get initial step for protocol type
     */
    public function getInitialStep(): string
    {
        $workflow = $this->getWorkflowConfig();
        return $workflow['initial_step'];
    }

    /**
     * Check if protocol is in final state
     */
    public function isInFinalState(): bool
    {
        $currentStep = $this->getCurrentStep();
        return empty($currentStep['next_steps']);
    }

    /**
     * Map workflow step to protocol status
     */
    protected function mapStepToStatus(string $step): string
    {
        return match($step) {
            'criacao' => 'rascunho',
            'aprovacao', 'aprovacao_honrarias', 'aprovacao_final' => 'em_analise',
            'definir_taxas' => 'pendente',
            'aguardando_pagamento' => 'aguardando_pagamento',
            'concluido' => 'concluido',
            'rejeitado' => 'rejeitado',
            default => 'pendente',
        };
    }

    /**
     * Get workflow progress percentage
     */
    public function getProgressPercentage(): int
    {
        $workflow = $this->getWorkflowConfig();
        $steps = array_keys($workflow['steps']);
        $currentStep = $this->protocolo->etapa_atual ?? $workflow['initial_step'];
        
        $currentIndex = array_search($currentStep, $steps);
        $totalSteps = count($steps);
        
        if ($currentIndex === false) {
            return 0;
        }
        
        return (int) (($currentIndex + 1) / $totalSteps * 100);
    }

    /**
     * Get step history for the protocol
     */
    public function getStepHistory(): Collection
    {
        return $this->protocolo->historico()
            ->where('tipo', 'mudanca_etapa')
            ->orderBy('created_at')
            ->get();
    }
}