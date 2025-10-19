<?php

namespace App\Policies;

use App\Models\Assembleia;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AssembleiaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Membros da jurisdição podem ver todas as assembleias
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }

        // Admin de assembleia pode ver apenas sua assembleia
        if ($user->hasRole('admin_assembleia')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Assembleia $assembleia): bool
    {
        // Membros da jurisdição podem ver todas as assembleias
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }

        // Admin de assembleia pode ver apenas sua assembleia
        if ($user->hasRole('admin_assembleia')) {
            // Verificar se o usuário pertence a esta assembleia
            $membro = $user->membro;
            return $membro && $membro->assembleia_id === $assembleia->id;
        }

        // Meninas ativas podem ver sua própria assembleia
        if ($user->hasRole(['menina_ativa', 'cargo_grande_assembleia'])) {
            $membro = $user->membro;
            return $membro && $membro->assembleia_id === $assembleia->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Apenas membros da jurisdição podem criar assembleias
        return $user->hasRole('membro_jurisdicao');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Assembleia $assembleia): bool
    {
        // Membros da jurisdição podem atualizar qualquer assembleia
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }

        // Admin de assembleia pode atualizar apenas sua assembleia
        if ($user->hasRole('admin_assembleia')) {
            $membro = $user->membro;
            return $membro && $membro->assembleia_id === $assembleia->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Assembleia $assembleia): bool
    {
        // Apenas membros da jurisdição podem deletar assembleias
        return $user->hasRole('membro_jurisdicao');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Assembleia $assembleia): bool
    {
        // Apenas membros da jurisdição podem restaurar assembleias
        return $user->hasRole('membro_jurisdicao');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Assembleia $assembleia): bool
    {
        // Apenas membros da jurisdição podem deletar permanentemente
        return $user->hasRole('membro_jurisdicao');
    }
}
