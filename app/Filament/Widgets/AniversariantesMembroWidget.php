<?php

namespace App\Filament\Widgets;

use App\Models\Membro;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AniversariantesMembroWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $assembleia_id = $user->membro?->assembleia_id;
        
        if (!$assembleia_id) {
            return [
                Stat::make('Aniversários Hoje', 0),
                Stat::make('Aniversários Esta Semana', 0),
                Stat::make('Aniversários Este Mês', 0),
            ];
        }

        $hoje = Carbon::now();
        
        // Aniversários hoje
        $aniversariosHoje = Membro::where('assembleia_id', $assembleia_id)
            ->whereDay('data_nascimento', $hoje->day)
            ->whereMonth('data_nascimento', $hoje->month)
            ->count();
        
        // Aniversários esta semana (próximos 7 dias)
        $inicioSemana = $hoje->copy();
        $fimSemana = $hoje->copy()->addDays(7);
        
        $aniversariosSemana = Membro::where('assembleia_id', $assembleia_id)
            ->where(function ($query) use ($inicioSemana, $fimSemana) {
                for ($data = $inicioSemana->copy(); $data <= $fimSemana; $data->addDay()) {
                    $query->orWhere(function ($q) use ($data) {
                        $q->whereDay('data_nascimento', $data->day)
                          ->whereMonth('data_nascimento', $data->month);
                    });
                }
            })
            ->count();
        
        // Aniversários este mês
        $aniversariosMes = Membro::where('assembleia_id', $assembleia_id)
            ->whereMonth('data_nascimento', $hoje->month)
            ->count();

        return [
            Stat::make('Aniversários Hoje', $aniversariosHoje)
                ->description($aniversariosHoje > 0 ? '🎂 Parabéns!' : '')
                ->color($aniversariosHoje > 0 ? 'success' : 'gray'),
                
            Stat::make('Aniversários Esta Semana', $aniversariosSemana)
                ->description('Próximos 7 dias')
                ->color('info'),
                
            Stat::make('Aniversários Este Mês', $aniversariosMes)
                ->description($hoje->monthName)
                ->color('warning'),
        ];
    }
}