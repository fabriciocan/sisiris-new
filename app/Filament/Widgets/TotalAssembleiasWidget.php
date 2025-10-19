<?php

namespace App\Filament\Widgets;

use App\Models\Assembleia;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalAssembleiasWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalAssembleias = Assembleia::count();
        $assembleiasAtivas = Assembleia::where('ativa', true)->count();
        $assembleiasInativas = Assembleia::where('ativa', false)->count();

        return [
            Stat::make('Total de Assembleias', $totalAssembleias)
                ->description('Total de assembleias na jurisdição')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),
                
            Stat::make('Assembleias Ativas', $assembleiasAtivas)
                ->description('Assembleias em funcionamento')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Assembleias Inativas', $assembleiasInativas)
                ->description('Assembleias suspensas')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}