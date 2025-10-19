<?php

namespace App\Filament\Resources\Protocolos\Pages;

use App\Filament\Resources\Protocolos\ProtocoloResource;
use App\Models\Protocolo;
use App\Services\IniciacaoService;
use App\Services\ProtocoloWorkflow;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApproveIniciacaoProtocolo extends ViewRecord
{
    protected static string $resource = ProtocoloResource::class;

    protected static ?string $title = 'Aprovar Protocolo de Iniciação';

    public function getHeading(): string
    {
        return 'Aprovação de Protocolo de Iniciação';
    }

    public function getSubheading(): ?string
    {
        return "Protocolo #{$this->record->numero_protocolo} - {$this->record->assembleia->nome}";
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Only membro_jurisdicao can approve
        if ($user && $user->hasRole('membro_jurisdicao') && $this->record->etapa_atual === 'aprovacao') {
            $actions[] = Actions\Action::make('approve')
                ->label('Aprovar Protocolo')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Aprovar Protocolo de Iniciação')
                ->modalDescription('Ao aprovar este protocolo, as novas meninas serão automaticamente cadastradas no sistema e receberão e-mails com suas credenciais de acesso.')
                ->modalSubmitActionLabel('Aprovar e Processar')
                ->action('approveProtocol');

            $actions[] = Actions\Action::make('reject')
                ->label('Rejeitar Protocolo')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Textarea::make('feedback_rejeicao')
                        ->label('Motivo da Rejeição')
                        ->required()
                        ->rows(4)
                        ->placeholder('Descreva o motivo da rejeição do protocolo...')
                ])
                ->action('rejectProtocol');
        }

        $actions[] = Actions\Action::make('back')
            ->label('Voltar')
            ->icon('heroicon-o-arrow-left')
            ->color('gray')
            ->url($this->getResource()::getUrl('index'));

        return $actions;
    }

    protected function getInfolistSchema(): array
    {
        return [
            Section::make('Informações do Protocolo')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('numero_protocolo')
                                ->label('Número do Protocolo')
                                ->content($this->record->numero_protocolo),

                            Placeholder::make('assembleia')
                                ->label('Assembleia')
                                ->content($this->record->assembleia->nome),

                            Placeholder::make('solicitante')
                                ->label('Solicitante')
                                ->content($this->record->solicitante->name ?? 'N/A'),

                            Placeholder::make('data_solicitacao')
                                ->label('Data de Solicitação')
                                ->content($this->record->data_solicitacao?->format('d/m/Y') ?? 'N/A'),

                            Placeholder::make('status')
                                ->label('Status')
                                ->content(ucfirst($this->record->status)),

                            Placeholder::make('etapa_atual')
                                ->label('Etapa Atual')
                                ->content(ucfirst(str_replace('_', ' ', $this->record->etapa_atual))),
                        ]),

                    Placeholder::make('descricao')
                        ->label('Descrição')
                        ->content($this->record->descricao)
                        ->columnSpanFull(),
                ]),

            Section::make('Novas Meninas para Iniciação')
                ->description('Lista das meninas que serão iniciadas')
                ->schema([
                    Placeholder::make('novas_meninas')
                        ->label('')
                        ->content(function () {
                            $novasMeninas = $this->record->dados_membros ?? [];
                            
                            if (empty($novasMeninas)) {
                                return 'Nenhuma menina cadastrada.';
                            }

                            $html = '<div class="space-y-4">';
                            
                            foreach ($novasMeninas as $index => $menina) {
                                $html .= '<div class="border rounded-lg p-4 bg-gray-50">';
                                $html .= '<h4 class="font-semibold text-lg mb-2">' . ($index + 1) . '. ' . ($menina['nome_completo'] ?? 'Nome não informado') . '</h4>';
                                
                                $html .= '<div class="grid grid-cols-2 gap-4 text-sm">';
                                $html .= '<div><strong>Data de Nascimento:</strong> ' . (isset($menina['data_nascimento']) ? \Carbon\Carbon::parse($menina['data_nascimento'])->format('d/m/Y') : 'N/A') . '</div>';
                                $html .= '<div><strong>CPF:</strong> ' . ($menina['cpf'] ?? 'N/A') . '</div>';
                                $html .= '<div><strong>Telefone:</strong> ' . ($menina['telefone'] ?? 'N/A') . '</div>';
                                $html .= '<div><strong>E-mail:</strong> ' . ($menina['email'] ?? 'N/A') . '</div>';
                                $html .= '<div><strong>Data de Iniciação:</strong> ' . (isset($menina['data_iniciacao']) ? \Carbon\Carbon::parse($menina['data_iniciacao'])->format('d/m/Y') : 'N/A') . '</div>';
                                
                                // Get madrinha name
                                $madrinhaName = 'N/A';
                                if (isset($menina['madrinha_id'])) {
                                    $madrinha = \App\Models\Membro::find($menina['madrinha_id']);
                                    $madrinhaName = $madrinha ? $madrinha->nome_completo : 'Madrinha não encontrada';
                                }
                                $html .= '<div><strong>Madrinha:</strong> ' . $madrinhaName . '</div>';
                                $html .= '</div>';
                                
                                $html .= '<div class="mt-2"><strong>Endereço:</strong> ' . ($menina['endereco_completo'] ?? 'N/A') . '</div>';
                                
                                if (!empty($menina['observacoes'])) {
                                    $html .= '<div class="mt-2"><strong>Observações:</strong> ' . $menina['observacoes'] . '</div>';
                                }
                                
                                $html .= '</div>';
                            }
                            
                            $html .= '</div>';
                            
                            return new \Illuminate\Support\HtmlString($html);
                        })
                        ->columnSpanFull(),
                ]),

            Section::make('Histórico do Protocolo')
                ->schema([
                    Placeholder::make('historico')
                        ->label('')
                        ->content(function () {
                            $historicos = $this->record->historico()->orderBy('created_at', 'desc')->get();
                            
                            if ($historicos->isEmpty()) {
                                return 'Nenhum histórico disponível.';
                            }

                            $html = '<div class="space-y-2">';
                            
                            foreach ($historicos as $historico) {
                                $html .= '<div class="flex justify-between items-center p-2 bg-gray-50 rounded">';
                                $html .= '<div>';
                                $html .= '<strong>' . ucfirst($historico->acao) . '</strong>';
                                if ($historico->descricao) {
                                    $html .= ' - ' . $historico->descricao;
                                }
                                $html .= '</div>';
                                $html .= '<div class="text-sm text-gray-600">';
                                $html .= $historico->created_at->format('d/m/Y H:i');
                                $html .= '</div>';
                                $html .= '</div>';
                            }
                            
                            $html .= '</div>';
                            
                            return new \Illuminate\Support\HtmlString($html);
                        })
                        ->columnSpanFull(),
                ]),
        ];
    }

    public function approveProtocol(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('membro_jurisdicao')) {
            Notification::make()
                ->danger()
                ->title('Acesso Negado')
                ->body('Apenas membros da jurisdição podem aprovar protocolos.')
                ->send();
            return;
        }

        try {
            DB::transaction(function () use ($user) {
                // Use workflow to transition to completed state
                $this->record->transitionTo('concluido', $user);

                // Update additional approval data
                $this->record->update([
                    'aprovado_por' => $user->id,
                    'data_aprovacao' => now(),
                ]);

                // The approval log will be handled by the Observer
            });

            Notification::make()
                ->success()
                ->title('Protocolo Aprovado')
                ->body('Protocolo aprovado com sucesso! As novas meninas serão cadastradas automaticamente e receberão e-mails com suas credenciais.')
                ->duration(8000)
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erro ao Processar Protocolo')
                ->body('Ocorreu um erro ao processar o protocolo: ' . $e->getMessage())
                ->duration(8000)
                ->send();
        }
    }

    public function rejectProtocol(array $data): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('membro_jurisdicao')) {
            Notification::make()
                ->danger()
                ->title('Acesso Negado')
                ->body('Apenas membros da jurisdição podem rejeitar protocolos.')
                ->send();
            return;
        }

        try {
            // Use workflow to transition to rejected state
            $this->record->transitionTo('rejeitado', $user, [
                'feedback_rejeicao' => $data['feedback_rejeicao']
            ]);

            // Update additional rejection data
            $this->record->update([
                'feedback_rejeicao' => $data['feedback_rejeicao'],
                'aprovado_por' => $user->id,
                'data_aprovacao' => now(),
            ]);

            // The rejection log will be handled by the Observer

            Notification::make()
                ->success()
                ->title('Protocolo Rejeitado')
                ->body('O protocolo foi rejeitado e o feedback foi enviado para o solicitante.')
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erro ao Rejeitar Protocolo')
                ->body('Ocorreu um erro ao rejeitar o protocolo: ' . $e->getMessage())
                ->send();
        }
    }

    protected function authorizeAccess(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user || !$user->hasRole('membro_jurisdicao')) {
            $this->redirect($this->getResource()::getUrl('index'));
        }
    }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        
        if ($this->record->tipo_protocolo !== 'iniciacao') {
            $this->redirect($this->getResource()::getUrl('index'));
        }

        $this->authorizeAccess();
    }
}