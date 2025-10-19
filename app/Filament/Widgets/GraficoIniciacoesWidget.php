<?php

namespace App\Filament\Widgets;

use App\Models\Membro;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class GraficoIniciacoesWidget extends ChartWidget
{
    protected static ?int $sort = 8;

    protected function getData(): array
    {
        $dados = [];
        $labels = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $iniciacoes = Membro::whereNotNull('data_iniciacao')
                ->whereYear('data_iniciacao', $mes->year)
                ->whereMonth('data_iniciacao', $mes->month)
                ->count();
                
            $dados[] = $iniciacoes;
            $labels[] = $mes->format('M/Y');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Iniciações',
                    'data' => $dados,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}