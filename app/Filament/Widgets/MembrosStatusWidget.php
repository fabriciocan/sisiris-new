<?php

namespace App\Filament\Widgets;

use App\Models\Membro;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class MembrosStatusWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $user = Auth::user();
        $assembleia_id = $user->membro?->assembleia_id;
        
        if (!$assembleia_id) {
            return [
                'datasets' => [['data' => []]],
                'labels' => [],
            ];
        }

        $membrosAtivas = Membro::where('assembleia_id', $assembleia_id)
            ->where('status', 'ativa')->count();
        $membrosCandidatas = Membro::where('assembleia_id', $assembleia_id)
            ->where('status', 'candidata')->count();
        $membrosAfastadas = Membro::where('assembleia_id', $assembleia_id)
            ->where('status', 'afastada')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Membros por Status',
                    'data' => [$membrosAtivas, $membrosCandidatas, $membrosAfastadas],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',   // success - ativa
                        'rgb(59, 130, 246)',  // blue - candidata
                        'rgb(251, 191, 36)',  // warning - afastada
                    ],
                ],
            ],
            'labels' => ['Ativas', 'Candidatas', 'Afastadas'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public static function canView(): bool
    {
        return true; // TODO: Implementar validação de role após configurar permissões
    }
}