<?php

namespace App\Filament\Resources\Protocolos\Pages;

use App\Filament\Resources\Protocolos\ProtocoloResource;
use App\Models\Protocolo;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action as PageAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApproveMaioridadeProtocolo extends Page
{
    protected static string $resource = ProtocoloResource::class;
    
    protected string $view = 'filament.resources.protocolos.pages.approve-maioridade-protocolo';
    
    protected static ?string $title = 'Aprovar Protocolo de Maioridade';
    
    public Protocolo $record;
    
    public ?string $dataCerimonia = null;
    public ?string $observacoes = null;

    public function mount(): void
    {
        // Check if user can approve this protocol
        if (!$this->canApprove()) {
            abort(403, 'Você não tem permissão para aprovar este protocolo.');
        }
        
        // Check if protocol is in correct state
        if (!in_array($this->record->etapa_atual, ['aguardando_aprovacao', 'aprovacao'])) {
            abort(403, 'Este protocolo não está na etapa de aprovação.');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            PageAction::make('approve')
                ->label('Aprovar Protocolo')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Confirmar Aprovação')
                ->modalDescription('Tem certeza que deseja aprovar este protocolo de maioridade?')
                ->modalSubmitActionLabel('Sim, Aprovar')
                ->action('approveProtocol'),
                
            PageAction::make('reject')
                ->label('Rejeitar Protocolo')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Confirmar Rejeição')
                ->modalDescription('Tem certeza que deseja rejeitar este protocolo?')
                ->modalSubmitActionLabel('Sim, Rejeitar')
                ->action('rejectProtocol'),
        ];
    }

    public function approveProtocol(): void
    {
        // Validate required fields
        if (empty($this->dataCerimonia)) {
            Notification::make()
                ->danger()
                ->title('Erro na Aprovação')
                ->body('A data da cerimônia é obrigatória para aprovação.')
                ->send();
            return;
        }
        
        try {
            DB::transaction(function () {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                
                // Update protocol with ceremony date first
                $this->record->update([
                    'data_cerimonia' => $this->dataCerimonia,
                    'observacoes' => $this->observacoes ?? '',
                ]);
                
                // Transition to completed state
                $this->record->transitionTo('concluido', $user, [
                    'aprovado' => true,
                    'data_cerimonia' => $this->dataCerimonia,
                    'observacoes' => $this->observacoes ?? '',
                ]);
            });
            
            Notification::make()
                ->success()
                ->title('Protocolo Aprovado')
                ->body('O protocolo foi aprovado com sucesso e as meninas foram promovidas à maioridade.')
                ->send();
                
            $this->redirect($this->getResource()::getUrl('index'));
            
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erro na Aprovação')
                ->body('Ocorreu um erro ao aprovar o protocolo: ' . $e->getMessage())
                ->send();
        }
    }

    public function rejectProtocol(): void
    {
        try {
            DB::transaction(function () {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                
                // Transition to rejected state
                $this->record->transitionTo('rejeitado', $user, [
                    'aprovado' => false,
                    'feedback_rejeicao' => 'Protocolo rejeitado pela jurisdição',
                ]);
            });
            
            Notification::make()
                ->success()
                ->title('Protocolo Rejeitado')
                ->body('O protocolo foi rejeitado e retornará para correção.')
                ->send();
                
            $this->redirect($this->getResource()::getUrl('index'));
            
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erro na Rejeição')
                ->body('Ocorreu um erro ao rejeitar o protocolo: ' . $e->getMessage())
                ->send();
        }
    }



    protected function canApprove(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        return $user && 
               $user->hasRole('membro_jurisdicao') && 
               $this->record->tipo_protocolo === 'maioridade';
    }


}