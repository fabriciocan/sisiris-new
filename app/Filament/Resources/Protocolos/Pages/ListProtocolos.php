<?php

namespace App\Filament\Resources\Protocolos\Pages;

use App\Filament\Resources\Protocolos\ProtocoloResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListProtocolos extends ListRecords
{
    protected static string $resource = ProtocoloResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Apenas admin_assembleia e membro_jurisdicao podem criar protocolos
        if ($user && ($user->hasRole('admin_assembleia') || $user->hasRole('membro_jurisdicao'))) {
            // Botão com ActionGroup (dropdown)
            $actions[] = ActionGroup::make([
                // Protocolos Simples
                Action::make('afastamento')
                    ->label('Afastamento')
                    ->icon('heroicon-o-user-minus')
                    ->url(route('filament.admin.resources.protocolos.create-afastamento'))
                    ->color('warning'),

                Action::make('maioridade')
                    ->label('Cerimônia de Maioridade')
                    ->icon('heroicon-o-cake')
                    ->disabled()
                    ->color('success'),

                // Protocolos de Iniciação
                Action::make('iniciacao')
                    ->label('Iniciação')
                    ->icon('heroicon-o-sparkles')
                    ->disabled()
                    ->color('info'),

                // Protocolos de Honrarias
                Action::make('homenageados_ano')
                    ->label('Homenageados do Ano')
                    ->icon('heroicon-o-star')
                    ->disabled()
                    ->color('amber'),

                Action::make('coracao_cores')
                    ->label('Coração das Cores')
                    ->icon('heroicon-o-heart')
                    ->disabled()
                    ->color('pink'),

                Action::make('grande_cruz_cores')
                    ->label('Grande Cruz das Cores')
                    ->icon('heroicon-o-trophy')
                    ->disabled()
                    ->color('yellow'),

                // Protocolos de Cargos
                Action::make('novos_cargos_assembleia')
                    ->label('Novos Cargos - Assembleia')
                    ->icon('heroicon-o-user-group')
                    ->disabled()
                    ->color('indigo'),

                Action::make('novos_cargos_conselho')
                    ->label('Novos Cargos - Conselho')
                    ->icon('heroicon-o-briefcase')
                    ->disabled()
                    ->color('purple'),
            ])
                ->label('Novo Protocolo')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->button();
        }

        return $actions;
    }
}

