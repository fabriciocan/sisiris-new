<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AniversariantesWidget;
use App\Filament\Widgets\CargosVagosWidget;
use App\Filament\Widgets\GraficoIniciacoesWidget;
use App\Filament\Widgets\MembrosStatusWidget;
use App\Filament\Widgets\PerfilUsuarioWidget;
use App\Filament\Widgets\ProtocolosPendentesWidget;
use App\Filament\Widgets\ProtocolosAssembleiaWidget;
use App\Filament\Widgets\ProximosEventosWidget;
use App\Filament\Widgets\TicketsAbertosWidget;
use App\Filament\Widgets\TotalAssembleiasWidget;
use App\Filament\Widgets\TotalMembrosWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        $user = Auth::user();
        
        // TODO: Implementar verificação de roles quando sistema de permissões estiver configurado
        // Por enquanto, mostra dashboard adaptado com foco na assembleia
        
        return [
            // Widgets principais da assembleia
            TotalMembrosWidget::class,
            MembrosStatusWidget::class,
            AniversariantesWidget::class,
            
            // Protocolos e tickets
            ProtocolosAssembleiaWidget::class,
            TicketsAbertosWidget::class,
            
            // Eventos e cargos
            ProximosEventosWidget::class,
            CargosVagosWidget::class,
            
            // Gráficos e estatísticas gerais
            GraficoIniciacoesWidget::class,
            
            // Informações do usuário (no final)
            PerfilUsuarioWidget::class,
        ];
    }
}