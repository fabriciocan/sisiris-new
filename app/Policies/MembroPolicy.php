<?php

namespace App\Policies;

use App\Models\Membro;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MembroPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'view_membros',
            'manage_membros'
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Membro $membro): bool
    {
        // Usuário pode ver seus próprios dados
        if ($user->membro && $user->membro->id === $membro->id) {
            return true;
        }

        // Membros da jurisdição podem ver todos os membros
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }

        // Admin de assembleia pode ver membros de sua assembleia
        if ($user->hasRole('admin_assembleia')) {
            $userMembro = $user->membro;
            return $userMembro && $userMembro->assembleia_id === $membro->assembleia_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Apenas membros da jurisdição podem criar novos membros
        return $user->hasRole('membro_jurisdicao');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Membro $membro): bool
    {
        // Apenas membros da jurisdição podem editar membros
        return $user->hasRole('membro_jurisdicao');
    }

    /**
     * Determine whether the user can manage member positions.
     */
    public function managePositions(User $user, Membro $membro): bool
    {
        if (!$user->hasAnyPermission(['assign_cargos_assembleia', 'assign_cargos_conselho'])) {
            return false;
        }

        // Membros da jurisdição podem gerenciar cargos de qualquer membro
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }

        // Admin de assembleia pode gerenciar cargos de membros de sua assembleia
        if ($user->hasRole('admin_assembleia')) {
            $userMembro = $user->membro;
            return $userMembro && $userMembro->assembleia_id === $membro->assembleia_id;
        }

        return false;
    }

    /**
     * Determine whether the user can manage member honors.
     */
    public function manageHonors(User $user, Membro $membro): bool
    {
        // Membros da jurisdição podem gerenciar honrarias de qualquer membro
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }

        // Admin de assembleia pode gerenciar honrarias de membros de sua assembleia
        if ($user->hasRole('admin_assembleia')) {
            $userMembro = $user->membro;
            return $userMembro && $userMembro->assembleia_id === $membro->assembleia_id;
        }

        return false;
    }

    /**
     * Determine whether the user can change member status (active/inactive).
     */
    public function changeStatus(User $user, Membro $membro): bool
    {
        // Apenas membros da jurisdição podem alterar status
        return $user->hasRole('membro_jurisdicao');
    }

    /**
     * Determine whether the user can promote member to maioridade.
     */
    public function promoteToMaioridade(User $user, Membro $membro): bool
    {
        // Verificar se o membro é menina ativa
        if (!$membro->user || !$membro->user->isMeninaAtiva()) {
            return false;
        }

        // Verificar se o membro é menina ativa
        if (!$membro->user || !$membro->user->isMeninaAtiva()) {
            return false;
        }

        // Membros da jurisdição podem promover qualquer menina ativa
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }

        // Admin de assembleia pode promover meninas ativas de sua assembleia
        if ($user->hasRole('admin_assembleia')) {
            $userMembro = $user->membro;
            return $userMembro && $userMembro->assembleia_id === $membro->assembleia_id;
        }

        return false;
    }

    /**
     * Determine whether the user can select member for protocols.
     */
    public function selectForProtocol(User $user, Membro $membro, string $protocolType): bool
    {
        // Verificar se o usuário pode criar o tipo de protocolo
        $canCreateProtocol = match($protocolType) {
            'maioridade' => $user->hasPermission('create_protocolo_maioridade'),
            'iniciacao' => $user->hasPermission('create_protocolo_iniciacao'),
            'homenageados_ano' => $user->hasPermission('create_protocolo_homenageados'),
            'coracao_cores' => $user->hasPermission('create_protocolo_coracao_cores'),
            'grande_cruz_cores' => $user->hasPermission('create_protocolo_grande_cruz'),
            'afastamento' => $user->hasPermission('create_protocolo_afastamento'),
            'cargos_assembleia' => $user->hasPermission('create_protocolo_cargos_assembleia'),
            'cargos_conselho' => $user->hasPermission('create_protocolo_cargos_conselho'),
            default => false,
        };

        if (!$canCreateProtocol) {
            return false;
        }

        // Membros da jurisdição podem selecionar qualquer membro
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }

        // Admin de assembleia pode selecionar membros de sua assembleia
        if ($user->hasRole('admin_assembleia')) {
            $userMembro = $user->membro;
            return $userMembro && $userMembro->assembleia_id === $membro->assembleia_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Membro $membro): bool
    {
        return $user->hasPermission('delete_membros') && $user->hasRole('membro_jurisdicao');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Membro $membro): bool
    {
        return $user->hasRole('membro_jurisdicao');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Membro $membro): bool
    {
        return $user->hasRole('super_admin');
    }
}
