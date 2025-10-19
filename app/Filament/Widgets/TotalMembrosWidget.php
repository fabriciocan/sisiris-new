<?php

namespace App\Filament\Widgets;

use App\Models\Membro;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TotalMembrosWidget extends ChartWidget
{
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $membrosAtivas = Membro::where('status', 'ativa')->count();
        $membrosCandidatas = Membro::where('status', 'candidata')->count();
        $membrosAfastadas = Membro::where('status', 'afastada')->count();
        $membrosMaioridade = Membro::where('status', 'maioridade')->count();
        $membrosDesligadas = Membro::where('status', 'desligada')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Membros por Status',
                    'data' => [$membrosAtivas, $membrosCandidatas, $membrosAfastadas, $membrosMaioridade, $membrosDesligadas],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',   // success - ativa
                        'rgb(59, 130, 246)',  // blue - candidata
                        'rgb(251, 191, 36)',  // warning - afastada
                        'rgb(156, 163, 175)', // gray - maioridade
                        'rgb(239, 68, 68)',   // danger - desligada
                    ],
                ],
            ],
            'labels' => ['Ativas', 'Candidatas', 'Afastadas', 'Maioridade', 'Desligadas'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}