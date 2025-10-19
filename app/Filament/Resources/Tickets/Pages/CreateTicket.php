<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Criar Novo Ticket';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Garantir que o solicitante é o usuário atual
        $data['solicitante_id'] = Auth::id();
        
        // Definir data de abertura
        $data['data_abertura'] = now();
        
        // Status inicial
        $data['status'] = 'aberto';
        
        return $data;
    }
}
