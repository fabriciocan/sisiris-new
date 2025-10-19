<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketsAbertosWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected function getStats(): array
    {
        $ticketsAbertos = Ticket::whereIn('status', ['aberto', 'em_atendimento'])->count();
        $ticketsUrgentes = Ticket::whereIn('status', ['aberto', 'em_atendimento'])
            ->where('prioridade', 'urgente')->count();
        $ticketsVencidos = Ticket::whereIn('status', ['aberto', 'em_atendimento'])
            ->where('data_abertura', '<', now()->subDays(3))->count();

        return [
            Stat::make('Tickets Abertos', $ticketsAbertos)
                ->description('Tickets em atendimento')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),
                
            Stat::make('Tickets Urgentes', $ticketsUrgentes)
                ->description('Prioridade urgente')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
                
            Stat::make('Tickets Vencidos', $ticketsVencidos)
                ->description('Mais de 3 dias abertos')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
        ];
    }
}