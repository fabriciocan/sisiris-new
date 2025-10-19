<?php

namespace App\Filament\Widgets;

use App\Models\Membro;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AniversariantesWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected function getStats(): array
    {
        $mesAtual = now()->month;
        
        $aniversariantesMes = Membro::whereMonth('data_nascimento', $mesAtual)
            ->where('status', 'ativa')
            ->count();
            
        $proximaSemana = Membro::whereBetween('data_nascimento', [
                now()->startOfWeek()->format('m-d'),
                now()->endOfWeek()->format('m-d')
            ])
            ->where('status', 'ativa')
            ->count();
            
        $hoje = Membro::whereDay('data_nascimento', now()->day)
            ->whereMonth('data_nascimento', now()->month)
            ->where('status', 'ativa')
            ->count();

        return [
            Stat::make('Aniversariantes Hoje', $hoje)
                ->description('Fazem aniversário hoje')
                ->descriptionIcon('heroicon-m-cake')
                ->color($hoje > 0 ? 'success' : 'gray'),
                
            Stat::make('Próximos 7 dias', $proximaSemana)
                ->description('Aniversários esta semana')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
                
            Stat::make('Aniversários ' . now()->format('M'), $aniversariantesMes)
                ->description('Aniversários neste mês')
                ->descriptionIcon('heroicon-m-gift')
                ->color('warning'),
        ];
    }
}