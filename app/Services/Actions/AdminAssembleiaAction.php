<?php

namespace App\Services\Actions;

use App\Models\Protocolo;
use App\Models\User;
use App\Models\ProtocoloHistorico;
use Illuminate\Support\Facades\Log;

class AdminAssembleiaAction implements ProtocoloActionInterface
{
    /**
     * Execute the action for admin assembleia steps
     */
    public function execute(Protocolo $protocolo, User $user, array $data = []): void
    {
        $etapaAtual = $protocolo->etapa_atual;
        
        switch ($etapaAtual) {
            case 'criacao':
                $this->handleCreation($protocolo, $user, $data);
                break;
                
            case 'aguardando_pagamento':
                $this->handlePayment($protocolo, $user, $data);
                break;
                
            default:
                $this->handleGenericAction($protocolo, $user, $data);
                break;
        }
        
        // Log the action
        $this->logAction($protocolo, $user, $etapaAtual, $data);
    }

    /**
     * Handle protocol creation
     */
    protected function handleCreation(Protocolo $protocolo, User $user, array $data): void
    {
        // Set initial data
        $protocolo->update([
            'solicitante_id' => $user->id,
            'data_solicitacao' => now(),
        ]);

        // Add members if provided
        if (isset($data['membros']) && is_array($data['membros'])) {
            foreach ($data['membros'] as $membroData) {
                if (isset($membroData['membro_id'])) {
                    $protocolo->addMembro(
                        \App\Models\Membro::find($membroData['membro_id']),
                        $membroData['pivot_data'] ?? []
                    );
                }
            }
        }

        // Set ceremony date if provided
        if (isset($data['data_cerimonia'])) {
            $protocolo->update(['data_cerimonia' => $data['data_cerimonia']]);
        }

        // Initialize workflow configuration
        $protocolo->initializeWorkflow();
    }

    /**
     * Handle payment submission
     */
    protected function handlePayment(Protocolo $protocolo, User $user, array $data): void
    {
        $updateData = [];

        // Handle payment receipt upload
        if (isset($data['comprovante_pagamento'])) {
            $updateData['comprovante_pagamento'] = $data['comprovante_pagamento'];
        }

        // Handle payment confirmation
        if (isset($data['pagamento_confirmado']) && $data['pagamento_confirmado']) {
            $updateData['status'] = 'pagamento_confirmado';
        }

        if (!empty($updateData)) {
            $protocolo->update($updateData);
        }
    }

    /**
     * Handle generic admin actions
     */
    protected function handleGenericAction(Protocolo $protocolo, User $user, array $data): void
    {
        // Handle general updates that admin can make
        $allowedFields = [
            'titulo',
            'descricao',
            'observacoes',
            'data_cerimonia',
            'comprovante_pagamento',
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

        // Handle member updates
        if (isset($data['membros_adicionados']) && is_array($data['membros_adicionados'])) {
            foreach ($data['membros_adicionados'] as $membroId) {
                $membro = \App\Models\Membro::find($membroId);
                if ($membro) {
                    $protocolo->addMembro($membro);
                }
            }
        }

        if (isset($data['membros_removidos']) && is_array($data['membros_removidos'])) {
            foreach ($data['membros_removidos'] as $membroId) {
                $membro = \App\Models\Membro::find($membroId);
                if ($membro) {
                    $protocolo->removeMembro($membro);
                }
            }
        }

        // Handle ceremony attendance
        if (isset($data['presenca_cerimonia']) && is_array($data['presenca_cerimonia'])) {
            $protocolo->definirPresencaCerimonia($data['presenca_cerimonia']);
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
            'tipo' => 'acao_admin',
            'descricao' => $descricao,
            'dados_anteriores' => $protocolo->getOriginal(),
            'dados_novos' => $protocolo->getAttributes(),
            'observacoes' => $data['observacoes_log'] ?? null,
        ]);

        Log::info('Admin Assembleia Action executed', [
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
            'criacao' => 'Protocolo criado pelo Admin da Assembleia',
            'aguardando_pagamento' => isset($data['comprovante_pagamento']) 
                ? 'Comprovante de pagamento anexado'
                : 'Dados de pagamento atualizados',
            default => 'Ação executada pelo Admin da Assembleia',
        };
    }

    /**
     * Validate if user can perform this action
     */
    public function canExecute(User $user, Protocolo $protocolo): bool
    {
        // Check if user has admin_assembleia role
        if (!$user->hasRole('admin_assembleia')) {
            return false;
        }

        // Check if user belongs to the same assembleia as the protocol
        if ($user->membro && $protocolo->assembleia_id !== $user->membro->assembleia_id) {
            return false;
        }

        return true;
    }

    /**
     * Get required data fields for this action
     */
    public function getRequiredFields(string $etapa): array
    {
        return match($etapa) {
            'criacao' => ['titulo', 'descricao'],
            'aguardando_pagamento' => ['comprovante_pagamento'],
            default => [],
        };
    }

    /**
     * Get optional data fields for this action
     */
    public function getOptionalFields(string $etapa): array
    {
        return match($etapa) {
            'criacao' => ['membros', 'data_cerimonia', 'observacoes'],
            'aguardando_pagamento' => ['observacoes'],
            default => ['observacoes'],
        };
    }
}