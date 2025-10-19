<?php

namespace App\Filament\Resources\Protocolos\Pages;

use App\Filament\Resources\Protocolos\ProtocoloResource;
use App\Models\Protocolo;
use App\Models\User;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

/**
 * Classe base para todas as páginas de protocolos
 * Fornece métodos auxiliares comuns
 */
abstract class BaseProtocoloPage extends Page
{
    protected static string $resource = ProtocoloResource::class;

    /**
     * Obtém o usuário autenticado tipado
     */
    protected function getAuthUser(): ?User
    {
        return Auth::user();
    }

    /**
     * Verifica se o usuário é admin da assembleia
     */
    protected function isAdminAssembleia(): bool
    {
        $user = $this->getAuthUser();
        return $user && $user->isAdminAssembleia();
    }

    /**
     * Verifica se o usuário é membro da jurisdição
     */
    protected function isMembroJurisdicao(): bool
    {
        $user = $this->getAuthUser();
        return $user && $user->isMembroJurisdicao();
    }

    /**
     * Verifica se o usuário é presidente de honrarias
     */
    protected function isPresidenteHonrarias(): bool
    {
        $user = $this->getAuthUser();
        return $user && $user->hasRole('presidente_honrarias');
    }

    /**
     * Obtém a assembleia do usuário autenticado
     */
    protected function getUserAssembleia(): ?int
    {
        $user = $this->getAuthUser();
        return $user?->membro?->assembleia_id;
    }

    /**
     * Verifica se o usuário pode acessar este protocolo
     */
    protected function canAccessProtocolo(Protocolo $protocolo): bool
    {
        $user = $this->getAuthUser();

        if (!$user) {
            return false;
        }

        // Membro jurisdição pode acessar todos
        if ($this->isMembroJurisdicao()) {
            return true;
        }

        // Admin assembleia pode acessar apenas da sua assembleia
        if ($this->isAdminAssembleia()) {
            return $protocolo->assembleia_id === $this->getUserAssembleia();
        }

        // Presidente honrarias pode acessar protocolos de honrarias
        if ($this->isPresidenteHonrarias()) {
            return in_array($protocolo->tipo_protocolo, [
                'homenageados_ano',
                'coracao_cores',
                'grande_cruz_cores',
            ]);
        }

        return false;
    }

    /**
     * Obtém o nome do tipo de protocolo formatado
     */
    protected function getTipoProtocoloLabel(string $tipo): string
    {
        return match ($tipo) {
            'maioridade' => 'Cerimônia de Maioridade',
            'iniciacao' => 'Iniciação',
            'homenageados_ano' => 'Homenageados do Ano',
            'coracao_cores' => 'Coração das Cores',
            'grande_cruz_cores' => 'Grande Cruz das Cores',
            'afastamento' => 'Afastamento',
            'novos_cargos_assembleia' => 'Novos Cargos - Assembleia',
            'novos_cargos_conselho' => 'Novos Cargos - Conselho',
            default => ucfirst(str_replace('_', ' ', $tipo)),
        };
    }

    /**
     * Obtém o nome do status formatado
     */
    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            'rascunho' => 'Rascunho',
            'pendente' => 'Pendente',
            'em_analise' => 'Em Análise',
            'aprovado' => 'Aprovado',
            'rejeitado' => 'Rejeitado',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            'aguardando_pagamento' => 'Aguardando Pagamento',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /**
     * Obtém a cor do status para badges
     */
    protected function getStatusColor(string $status): string
    {
        return match ($status) {
            'rascunho' => 'gray',
            'pendente' => 'warning',
            'em_analise' => 'info',
            'aprovado' => 'success',
            'rejeitado' => 'danger',
            'concluido' => 'success',
            'cancelado' => 'danger',
            'aguardando_pagamento' => 'warning',
            default => 'gray',
        };
    }

    /**
     * Obtém mensagem de sucesso padrão
     */
    protected function getSuccessMessage(string $acao): string
    {
        return match ($acao) {
            'criado' => 'Protocolo criado com sucesso!',
            'atualizado' => 'Protocolo atualizado com sucesso!',
            'aprovado' => 'Protocolo aprovado com sucesso!',
            'rejeitado' => 'Protocolo rejeitado.',
            'enviado' => 'Protocolo enviado para aprovação!',
            'concluido' => 'Protocolo concluído com sucesso!',
            default => 'Operação realizada com sucesso!',
        };
    }

    /**
     * Envia notificação de sucesso
     */
    protected function notifySuccess(string $acao): void
    {
        \Filament\Notifications\Notification::make()
            ->success()
            ->title($this->getSuccessMessage($acao))
            ->send();
    }

    /**
     * Envia notificação de erro
     */
    protected function notifyError(string $message): void
    {
        \Filament\Notifications\Notification::make()
            ->danger()
            ->title('Erro')
            ->body($message)
            ->send();
    }

    /**
     * Envia notificação de aviso
     */
    protected function notifyWarning(string $message): void
    {
        \Filament\Notifications\Notification::make()
            ->warning()
            ->title('Atenção')
            ->body($message)
            ->send();
    }

    /**
     * Envia notificação informativa
     */
    protected function notifyInfo(string $message): void
    {
        \Filament\Notifications\Notification::make()
            ->info()
            ->title('Informação')
            ->body($message)
            ->send();
    }

    /**
     * Redireciona para a lista de protocolos
     */
    protected function redirectToList(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('filament.admin.resources.protocolos.index');
    }

    /**
     * Redireciona para visualização do protocolo
     */
    protected function redirectToView(Protocolo $protocolo): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('filament.admin.resources.protocolos.view', $protocolo);
    }

    /**
     * Redireciona para edição do protocolo
     */
    protected function redirectToEdit(Protocolo $protocolo): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('filament.admin.resources.protocolos.edit', $protocolo);
    }
}
