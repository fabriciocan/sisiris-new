<?php

namespace App\Filament\Resources\Protocolos\Pages\Afastamento;

use App\Helpers\ProtocoloLogger;
use App\Models\Protocolo;
use App\Services\AfastamentoService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ApproveAfastamentoProtocolo extends Page
{
    protected static string $resource = \App\Filament\Resources\Protocolos\ProtocoloResource::class;

    protected string $view = 'filament.resources.protocolos.pages.approve-afastamento';

    public Protocolo $record;

    public string $decisao = '';
    public string $observacoes_aprovacao = '';
    public string $feedback_rejeicao = '';

    public function mount(Protocolo $record): void
    {
        // Verificar permissão
        $user = Auth::user();
        if (!$user || !$user->hasRole('membro_jurisdicao')) {
            Notification::make()
                ->danger()
                ->title('Acesso Negado')
                ->body('Apenas membros da jurisdição podem aprovar protocolos.')
                ->send();

            $this->redirect(route('filament.admin.resources.protocolos.index'));
            return;
        }

        // Verificar se protocolo está pendente
        if (!in_array($record->status, ['pendente', 'em_analise'])) {
            Notification::make()
                ->warning()
                ->title('Protocolo já Processado')
                ->body('Este protocolo já foi aprovado ou rejeitado.')
                ->send();

            $this->redirect(route('filament.admin.resources.protocolos.index'));
            return;
        }

        $this->record = $record->load('membro', 'assembleia', 'solicitante');
    }

    public function getHeading(): string
    {
        return 'Aprovar Protocolo de Afastamento';
    }

    public function getSubheading(): ?string
    {
        return "Protocolo: {$this->record->numero_protocolo}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('aprovar')
                ->label('Aprovar Protocolo')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Aprovar Afastamento')
                ->modalDescription('Tem certeza que deseja APROVAR este afastamento? O membro será marcado como inativo.')
                ->modalSubmitActionLabel('Sim, Aprovar')
                ->action('aprovar'),

            Action::make('rejeitar')
                ->label('Rejeitar Protocolo')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Rejeitar Protocolo')
                ->modalDescription('Tem certeza que deseja REJEITAR este protocolo?')
                ->modalSubmitActionLabel('Sim, Rejeitar')
                ->form([
                    \Filament\Forms\Components\Textarea::make('feedback_rejeicao')
                        ->label('Motivo da Rejeição')
                        ->required()
                        ->rows(4)
                        ->placeholder('Descreva o motivo da rejeição...')
                ])
                ->action(function (array $data) {
                    $this->rejeitar($data['feedback_rejeicao']);
                }),

            Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(route('filament.admin.resources.protocolos.index')),
        ];
    }

    public function aprovar(): void
    {
        try {
            $user = Auth::user();
            $afastamentoService = app(AfastamentoService::class);

            // Processar afastamento
            $resultado = $afastamentoService->processarAfastamento(
                $this->record,
                $user,
                $this->observacoes_aprovacao
            );

            if ($resultado['sucesso']) {
                // Atualizar protocolo
                $this->record->update([
                    'status' => 'concluido',
                    'etapa_atual' => 'concluido',
                    'aprovado_por' => $user->id,
                    'data_aprovacao' => now(),
                    'data_conclusao' => now(),
                ]);

                // Log de aprovação
                ProtocoloLogger::logAprovacao(
                    $this->record,
                    $this->observacoes_aprovacao ?: 'Protocolo aprovado',
                    $user
                );

                // Log de conclusão
                ProtocoloLogger::logConclusao(
                    $this->record,
                    "Membro afastado com sucesso",
                    $user
                );

                Notification::make()
                    ->success()
                    ->title('Protocolo Aprovado!')
                    ->body($resultado['mensagem'])
                    ->send();

                $this->redirect(route('filament.admin.resources.protocolos.index'));
            } else {
                throw new \Exception($resultado['mensagem']);
            }
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erro ao Aprovar')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function rejeitar(string $feedback): void
    {
        try {
            $user = Auth::user();

            // Atualizar protocolo - marcar como concluído mesmo sendo rejeitado
            $this->record->update([
                'status' => 'concluido',
                'etapa_atual' => 'rejeitado',
                'feedback_rejeicao' => $feedback,
                'aprovado_por' => $user->id,
                'data_aprovacao' => now(),
                'data_conclusao' => now(),
            ]);

            // Log de rejeição
            ProtocoloLogger::logRejeicao(
                $this->record,
                $feedback,
                $user
            );

            // Log de conclusão (mesmo rejeitado, o protocolo está concluído)
            ProtocoloLogger::logConclusao(
                $this->record,
                "Protocolo concluído com rejeição: {$feedback}",
                $user
            );

            Notification::make()
                ->warning()
                ->title('Protocolo Rejeitado')
                ->body('O protocolo foi rejeitado e marcado como concluído. O admin da assembleia foi notificado.')
                ->send();

            $this->redirect(route('filament.admin.resources.protocolos.index'));
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erro ao Rejeitar')
                ->body($e->getMessage())
                ->send();
        }
    }

    /**
     * Get status label
     */
    public function getStatusLabel(string $status): string
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
            default => ucfirst($status),
        };
    }
}
