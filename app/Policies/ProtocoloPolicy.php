<?php

namespace App\Policies;

use App\Models\Protocolo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProtocoloPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin_assembleia', 'membro_jurisdicao', 'presidente_honrarias']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Protocolo $protocolo): bool
    {
        // Membro jurisdição pode ver todos
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }
        
        // Admin assembleia pode ver apenas da sua assembleia
        if ($user->hasRole('admin_assembleia') && $user->membro) {
            return $protocolo->assembleia_id === $user->membro->assembleia_id;
        }

        // Presidente de honrarias pode ver protocolos de honrarias
        if ($user->hasRole('presidente_honrarias')) {
            return in_array($protocolo->tipo_protocolo, [
                'homenageados_ano', 'coracao_cores', 'grande_cruz_cores'
            ]);
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin_assembleia', 'membro_jurisdicao']);
    }

    /**
     * Determine whether the user can create specific protocol types.
     */
    public function createMaioridade(User $user): bool
    {
        return $user->hasRole(['admin_assembleia', 'membro_jurisdicao']);
    }

    public function createIniciacao(User $user): bool
    {
        return $user->hasRole(['admin_assembleia', 'membro_jurisdicao']);
    }

    public function createHomenageados(User $user): bool
    {
        return $user->hasRole(['admin_assembleia', 'membro_jurisdicao']);
    }

    public function createCoracaoCores(User $user): bool
    {
        return $user->hasRole(['admin_assembleia', 'membro_jurisdicao']);
    }

    public function createGrandeCruz(User $user): bool
    {
        return $user->hasRole(['admin_assembleia', 'membro_jurisdicao']);
    }

    public function createAfastamento(User $user): bool
    {
        return $user->hasRole(['admin_assembleia', 'membro_jurisdicao']);
    }

    public function createCargosAssembleia(User $user): bool
    {
        return $user->hasRole(['admin_assembleia', 'membro_jurisdicao']);
    }

    public function createCargosConselho(User $user): bool
    {
        return $user->hasRole(['admin_assembleia', 'membro_jurisdicao']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Protocolo $protocolo): bool
    {
        // Membro jurisdição pode editar todos (para aprovação)
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }
        
        // Admin assembleia pode editar apenas os próprios protocolos
        if ($user->hasRole('admin_assembleia') && $user->membro) {
            return $protocolo->assembleia_id === $user->membro->assembleia_id;
        }

        // Presidente de honrarias pode editar protocolos de honrarias
        if ($user->hasRole('presidente_honrarias')) {
            return in_array($protocolo->tipo_protocolo, [
                'homenageados_ano', 'coracao_cores', 'grande_cruz_cores'
            ]);
        }
        
        return false;
    }

    /**
     * Determine whether the user can approve protocols.
     */
    public function approve(User $user, Protocolo $protocolo): bool
    {
        // Membro jurisdição pode aprovar todos os protocolos
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }

        // Presidente de honrarias pode aprovar protocolos de honrarias
        if ($user->hasRole('presidente_honrarias')) {
            return in_array($protocolo->tipo_protocolo, [
                'homenageados_ano', 'coracao_cores', 'grande_cruz_cores'
            ]);
        }

        return false;
    }

    /**
     * Determine whether the user can approve specific protocol types.
     */
    public function approveMaioridade(User $user): bool
    {
        return $user->hasRole('membro_jurisdicao');
    }

    public function approveIniciacao(User $user): bool
    {
        return $user->hasRole('membro_jurisdicao');
    }

    public function approveHomenageados(User $user): bool
    {
        return $user->hasRole(['membro_jurisdicao', 'presidente_honrarias']);
    }

    public function approveCoracaoCores(User $user): bool
    {
        return $user->hasRole(['membro_jurisdicao', 'presidente_honrarias']);
    }

    public function approveGrandeCruz(User $user): bool
    {
        return $user->hasRole(['membro_jurisdicao', 'presidente_honrarias']);
    }

    public function approveAfastamento(User $user): bool
    {
        return $user->hasRole('membro_jurisdicao');
    }

    public function approveCargosAssembleia(User $user): bool
    {
        return $user->hasRole('membro_jurisdicao');
    }

    public function approveCargosConselho(User $user): bool
    {
        return $user->hasRole('membro_jurisdicao');
    }

    /**
     * Determine whether the user can manage protocol taxes.
     */
    public function manageTaxes(User $user, Protocolo $protocolo): bool
    {
        return $user->hasRole('membro_jurisdicao');
    }

    /**
     * Determine whether the user can reject protocols.
     */
    public function reject(User $user, Protocolo $protocolo): bool
    {
        return $this->approve($user, $protocolo);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Protocolo $protocolo): bool
    {
        // Apenas super admin pode excluir protocolos
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Protocolo $protocolo): bool
    {
        // Apenas super admin pode restaurar protocolos
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Protocolo $protocolo): bool
    {
        // Apenas super admin pode excluir permanentemente protocolos
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can view protocol logs.
     */
    public function viewLogs(User $user, Protocolo $protocolo): bool
    {
        return $user->hasRole(['admin_assembleia', 'membro_jurisdicao']) && $this->view($user, $protocolo);
    }

    /**
     * Determine whether the user can select assembleia when creating protocols.
     */
    public function selectAssembleia(User $user): bool
    {
        return $user->hasRole('membro_jurisdicao');
    }
}
