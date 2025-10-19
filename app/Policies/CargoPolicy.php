<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Membro;
use Illuminate\Auth\Access\Response;

class CargoPolicy
{
    /**
     * Determine whether the user can assign assembleia positions.
     */
    public function assignAssembleiaPosition(User $user): bool
    {
        return $user->hasPermission('assign_cargos_assembleia');
    }

    /**
     * Determine whether the user can assign conselho positions.
     */
    public function assignConselhoPosition(User $user): bool
    {
        return $user->hasPermission('assign_cargos_conselho');
    }

    /**
     * Determine whether the user can assign a specific assembleia position to a member.
     */
    public function assignSpecificAssembleiaPosition(User $user, Membro $membro, string $cargoTipo): bool
    {
        if (!$this->assignAssembleiaPosition($user)) {
            return false;
        }

        // Verificar se o membro é elegível para cargos de assembleia (apenas meninas ativas)
        if (!$membro->user || !$membro->user->isMeninaAtiva()) {
            return false;
        }

        // Membros da jurisdição podem atribuir cargos em qualquer assembleia
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }

        // Admin de assembleia pode atribuir cargos apenas em sua assembleia
        if ($user->hasRole('admin_assembleia')) {
            $userMembro = $user->membro;
            return $userMembro && $userMembro->assembleia_id === $membro->assembleia_id;
        }

        return false;
    }

    /**
     * Determine whether the user can assign a specific conselho position to a member.
     */
    public function assignSpecificConselhoPosition(User $user, Membro $membro, string $cargoTipo): bool
    {
        if (!$this->assignConselhoPosition($user)) {
            return false;
        }

        // Verificar elegibilidade por tipo de usuário para cargos do conselho
        $eligibleUserTypes = $this->getEligibleUserTypesForConselhoPosition($cargoTipo);
        
        if (!$membro->user || !in_array($membro->user->tipoUsuario?->codigo, $eligibleUserTypes)) {
            return false;
        }

        // Verificação especial para Presidente do Conselho Consultivo
        if ($cargoTipo === 'presidente_conselho') {
            if (!$membro->user->isTioMacom() || $membro->grau_maconico !== 'mestre') {
                return false;
            }
        }

        // Membros da jurisdição podem atribuir cargos em qualquer assembleia
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }

        // Admin de assembleia pode atribuir cargos apenas em sua assembleia
        if ($user->hasRole('admin_assembleia')) {
            $userMembro = $user->membro;
            return $userMembro && $userMembro->assembleia_id === $membro->assembleia_id;
        }

        return false;
    }

    /**
     * Determine whether the user can remove positions.
     */
    public function removePosition(User $user, Membro $membro): bool
    {
        if (!$user->hasAnyPermission(['assign_cargos_assembleia', 'assign_cargos_conselho'])) {
            return false;
        }

        // Membros da jurisdição podem remover cargos de qualquer membro
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }

        // Admin de assembleia pode remover cargos de membros de sua assembleia
        if ($user->hasRole('admin_assembleia')) {
            $userMembro = $user->membro;
            return $userMembro && $userMembro->assembleia_id === $membro->assembleia_id;
        }

        return false;
    }

    /**
     * Determine whether the user can view position assignments.
     */
    public function viewPositions(User $user): bool
    {
        return $user->hasPermission('view_cargos');
    }

    /**
     * Determine whether the user can manage position types.
     */
    public function managePositionTypes(User $user): bool
    {
        return $user->hasPermission('manage_cargos');
    }

    /**
     * Get eligible user types for conselho positions.
     */
    private function getEligibleUserTypesForConselhoPosition(string $cargoTipo): array
    {
        return match($cargoTipo) {
            'presidente_conselho' => ['tio_macom'], // Apenas Tio Maçom Mestre
            'preceptora_mae', 'preceptora_mae_adjunta' => [
                'tio_macom', 'tia_estrela', 'maioridade', 'tio', 'tia'
            ],
            'membro_conselho' => [
                'tio_macom', 'tia_estrela', 'maioridade', 'tio', 'tia'
            ],
            default => []
        };
    }

    /**
     * Determine whether a position grants admin access.
     */
    public function positionGrantsAdminAccess(string $cargoTipo): bool
    {
        return in_array($cargoTipo, [
            'presidente_conselho',
            'preceptora_mae',
            'preceptora_mae_adjunta'
        ]);
    }

    /**
     * Determine whether the user can create position assignment protocols.
     */
    public function createPositionProtocol(User $user, string $protocolType): bool
    {
        return match($protocolType) {
            'cargos_assembleia' => $user->hasPermission('create_protocolo_cargos_assembleia'),
            'cargos_conselho' => $user->hasPermission('create_protocolo_cargos_conselho'),
            default => false,
        };
    }

    /**
     * Determine whether the user can approve position assignment protocols.
     */
    public function approvePositionProtocol(User $user, string $protocolType): bool
    {
        return match($protocolType) {
            'cargos_assembleia' => $user->hasPermission('approve_protocolo_cargos_assembleia'),
            'cargos_conselho' => $user->hasPermission('approve_protocolo_cargos_conselho'),
            default => false,
        };
    }
}