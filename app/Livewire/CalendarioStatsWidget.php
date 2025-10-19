<?php

namespace App\Livewire;

use App\Models\EventoCalendario;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CalendarioStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $hoje = Carbon::now();
        $inicioMes = $hoje->copy()->startOfMonth();
        $fimMes = $hoje->copy()->endOfMonth();
        
        // Eventos deste mês
        $eventosMes = EventoCalendario::whereBetween('data_inicio', [$inicioMes, $fimMes])->count();
        
        // Eventos próximos (próximos 7 dias)
        $eventosProximos = EventoCalendario::whereBetween('data_inicio', [$hoje, $hoje->copy()->addDays(7)])->count();
        
        // Eventos hoje
        $eventosHoje = EventoCalendario::whereDate('data_inicio', $hoje)->count();
        
        // Eventos públicos deste mês
        $eventosPublicos = EventoCalendario::whereBetween('data_inicio', [$inicioMes, $fimMes])
            ->where('publico', true)
            ->count();

        return [
            Stat::make('Eventos Hoje', $eventosHoje)
                ->description('Eventos agendados para hoje')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),
                
            Stat::make('Próximos 7 Dias', $eventosProximos)
                ->description('Eventos dos próximos 7 dias')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Este Mês', $eventosMes)
                ->description('Total de eventos em ' . $hoje->format('F'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
                
            Stat::make('Eventos Públicos', $eventosPublicos)
                ->description('Eventos públicos deste mês')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
        ];
    }
}
