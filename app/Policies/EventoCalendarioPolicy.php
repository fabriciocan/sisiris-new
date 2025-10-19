<?php

namespace App\Policies;

use App\Models\EventoCalendario;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventoCalendarioPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Todos podem ver a lista de eventos
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EventoCalendario $eventoCalendario): bool
    {
        // Eventos públicos podem ser vistos por todos
        if ($eventoCalendario->publico) {
            return true;
        }

        // Eventos privados só podem ser vistos por membros da assembleia ou admin geral
        if ($user->hasRole('admin_geral')) {
            return true;
        }

        if ($user->hasRole('admin_assembleia') && $eventoCalendario->assembleia_id === $user->assembleia_id) {
            return true;
        }

        if ($user->hasRole('membro_jurisdicao') && $eventoCalendario->assembleia_id === $user->assembleia_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admins e membros da jurisdição podem criar eventos
        return $user->hasAnyRole(['admin_geral', 'admin_assembleia', 'membro_jurisdicao']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EventoCalendario $eventoCalendario): bool
    {
        // Admin geral pode editar qualquer evento
        if ($user->hasRole('admin_geral')) {
            return true;
        }

        // Admin da assembleia pode editar eventos de sua assembleia
        if ($user->hasRole('admin_assembleia') && $eventoCalendario->assembleia_id === $user->assembleia_id) {
            return true;
        }

        // Criador do evento pode editá-lo
        if ($eventoCalendario->criado_por === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EventoCalendario $eventoCalendario): bool
    {
        // Admin geral pode deletar qualquer evento
        if ($user->hasRole('admin_geral')) {
            return true;
        }

        // Admin da assembleia pode deletar eventos de sua assembleia
        if ($user->hasRole('admin_assembleia') && $eventoCalendario->assembleia_id === $user->assembleia_id) {
            return true;
        }

        // Criador do evento pode deletá-lo
        if ($eventoCalendario->criado_por === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EventoCalendario $eventoCalendario): bool
    {
        return $this->delete($user, $eventoCalendario);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EventoCalendario $eventoCalendario): bool
    {
        return $user->hasRole('admin_geral');
    }
}
