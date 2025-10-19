<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos os usuários autenticados podem ver a lista de tickets
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Admin jurisdição pode ver todos
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }
        
        // Admin assembleia pode ver tickets da sua assembleia
        if ($user->hasRole('admin_assembleia') && $user->membro) {
            return $ticket->assembleia_id === $user->membro->assembleia_id;
        }
        
        // Usuários podem ver tickets que criaram ou estão atribuídos a eles
        return $ticket->solicitante_id === $user->id || $ticket->responsavel_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Todos os usuários autenticados podem criar tickets
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Admin jurisdição pode editar todos
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }
        
        // Admin assembleia pode editar tickets da sua assembleia
        if ($user->hasRole('admin_assembleia') && $user->membro) {
            return $ticket->assembleia_id === $user->membro->assembleia_id;
        }
        
        // Usuários podem editar apenas tickets que criaram (se ainda não foi atribuído)
        return $ticket->solicitante_id === $user->id && $ticket->status === 'aberto';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Apenas admin jurisdição pode deletar tickets
        if ($user->hasRole('membro_jurisdicao')) {
            return true;
        }
        
        // Admin assembleia pode deletar tickets da sua assembleia se ainda não iniciados
        if ($user->hasRole('admin_assembleia') && $user->membro) {
            return $ticket->assembleia_id === $user->membro->assembleia_id && $ticket->status === 'aberto';
        }
        
        // Usuários podem deletar apenas tickets próprios que ainda não foram atribuídos
        return $ticket->solicitante_id === $user->id && $ticket->status === 'aberto';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false;
    }
}
