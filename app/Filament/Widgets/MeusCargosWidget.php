<?php

namespace App\Filament\Widgets;

use App\Models\HistoricoCargo;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MeusCargosWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $membro = $user->membro;
        
        if (!$membro) {
            return [
                Stat::make('Cargos Atuais', 0),
                Stat::make('Total de Cargos', 0),
            ];
        }

        // Cargos atuais
        $cargosAtuais = HistoricoCargo::where('membro_id', $membro->id)
            ->whereNull('data_fim')
            ->count();

        // Total de cargos já ocupados
        $totalCargos = HistoricoCargo::where('membro_id', $membro->id)
            ->count();

        // Cargo atual principal (mais recente)
        $cargoAtual = HistoricoCargo::with(['cargoAssembleia', 'cargoGrandeAssembleia'])
            ->where('membro_id', $membro->id)
            ->whereNull('data_fim')
            ->orderBy('data_inicio', 'desc')
            ->first();

        $cargoAtualNome = 'Nenhum cargo';
        if ($cargoAtual) {
            $cargo = $cargoAtual->cargoAssembleia ?? $cargoAtual->cargoGrandeAssembleia;
            $cargoAtualNome = $cargo->nome ?? 'Cargo não encontrado';
        }

        return [
            Stat::make('Cargos Atuais', $cargosAtuais)
                ->description($cargoAtualNome)
                ->color($cargosAtuais > 0 ? 'success' : 'gray'),
                
            Stat::make('Total de Cargos', $totalCargos)
                ->description('Histórico completo')
                ->color('info'),
        ];
    }
}