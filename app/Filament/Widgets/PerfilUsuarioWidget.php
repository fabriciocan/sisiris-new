<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class PerfilUsuarioWidget extends BaseWidget
{
    protected static ?int $sort = 100; // Coloca no final do dashboard
    
    protected function getStats(): array
    {
        $user = Auth::user();
        $membro = $user->membro;
        
        if (!$membro) {
            return [
                Stat::make('Usuário', $user->name)
                    ->description('Sem vínculo com membro')
                    ->color('gray'),
                    
                Stat::make('Email', $user->email)
                    ->description('Usuário do sistema')
                    ->color('info'),
            ];
        }

        // Calcular tempo como membro
        $dataIniciacao = $membro->data_iniciacao;
        $tempoComoMembro = $dataIniciacao ? 
            $dataIniciacao->diffInYears(Carbon::now()) . ' anos' : 
            'Não informado';

        // Status do membro
        $statusMembro = match($membro->status) {
            'ativo' => 'Ativo',
            'inativo' => 'Inativo',
            'suspenso' => 'Suspenso',
            'transferido' => 'Transferido',
            default => 'Não definido'
        };

        return [
            Stat::make('Membro', $membro->nome_completo ?? $user->name)
                ->description($membro->assembleia->nome ?? 'Assembleia não definida')
                ->color('success'),
                
            Stat::make('Status', $statusMembro)
                ->description("Membro há {$tempoComoMembro}")
                ->color($membro->status === 'ativo' ? 'success' : 'warning'),
                
            Stat::make('Grau', $membro->grau ?? 'Não informado')
                ->description('Grau maçônico atual')
                ->color('info'),
                
            Stat::make('CIM', $membro->cim ?? 'Não informado')
                ->description('Carteira de Identidade Maçônica')
                ->color('gray'),
        ];
    }
}